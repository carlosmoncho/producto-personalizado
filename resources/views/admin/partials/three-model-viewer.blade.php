{{--
    Visor de modelo 3D interactivo (Réplica exacta del frontend)
    Usa el mesh "LogoArea" del modelo GLB para proyectar el logo
    Carga HDRI para iluminación realista igual que el frontend
--}}

@php
    $viewerId = $viewerId ?? 'three-viewer-' . uniqid();
    $funcId = str_replace('-', '_', $viewerId);
    $defaultTransform = ['x' => 0, 'y' => 0, 'scale' => 1, 'rotation' => 0];
    $finalLogoTransform = $logoTransform ?? $defaultTransform;

    // Convertir URLs de S3 a proxy local para evitar problemas de CORS
    $finalModelUrl = $modelUrl ?? '';
    if ($finalModelUrl && str_contains($finalModelUrl, 's3.') && str_contains($finalModelUrl, 'amazonaws.com')) {
        // Extraer el path del archivo (ej: 3d-models/xxx.glb)
        if (preg_match('/amazonaws\.com\/(.+)$/', $finalModelUrl, $matches)) {
            $finalModelUrl = '/api/storage/' . $matches[1];
        }
    }

    // También para el logo si es de S3
    $finalLogoUrl = $logoUrl ?? null;
    if ($finalLogoUrl && str_contains($finalLogoUrl, 's3.') && str_contains($finalLogoUrl, 'amazonaws.com')) {
        if (preg_match('/amazonaws\.com\/(.+)$/', $finalLogoUrl, $matches)) {
            $finalLogoUrl = '/api/storage/' . $matches[1];
        }
    }
@endphp

<div id="{{ $viewerId }}" class="three-model-viewer" style="width: 100%; height: 500px; background: linear-gradient(to bottom, #e0e7ff 0%, #ffffff 100%); border-radius: 12px; overflow: hidden; position: relative;">
    <div class="viewer-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; z-index: 10;">
        <div class="spinner-border text-primary mb-2" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="text-muted">Cargando modelo 3D...</p>
    </div>
</div>

<div class="mt-3 d-flex gap-2 flex-wrap justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset-{{ $funcId }}">
        <i class="bi bi-arrows-fullscreen me-1"></i>Resetear vista
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-rotate-{{ $funcId }}">
        <i class="bi bi-arrow-repeat me-1"></i>Auto-rotar
    </button>
    <button type="button" class="btn btn-sm btn-primary" id="btn-download-{{ $funcId }}">
        <i class="bi bi-download me-1"></i>Descargar imagen
    </button>
</div>

@pushOnce('styles')
<style>
.three-model-viewer canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
}
</style>
@endPushOnce

@push('scripts')
<script type="module">
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.181.0/build/three.module.js';
import { GLTFLoader } from 'https://cdn.jsdelivr.net/npm/three@0.181.0/examples/jsm/loaders/GLTFLoader.js';
import { OrbitControls } from 'https://cdn.jsdelivr.net/npm/three@0.181.0/examples/jsm/controls/OrbitControls.js';
import { RGBELoader } from 'https://cdn.jsdelivr.net/npm/three@0.181.0/examples/jsm/loaders/RGBELoader.js';

(function() {
    const containerId = '{{ $viewerId }}';
    const funcId = '{{ $funcId }}';
    const modelUrl = @json($finalModelUrl);
    const colorHex = @json($colorHex ?? '#FFFFFF');
    const logoUrl = @json($finalLogoUrl);
    const logoTransform = @json($finalLogoTransform);

    // Habilitar gestión de color igual que React Three Fiber
    THREE.ColorManagement.enabled = true;

    let scene, camera, renderer, controls, model;
    let autoRotate = false;
    let initialized = false;
    let pmremGenerator = null;
    let envMap = null;

    // Referencias para el logo (igual que frontend)
    let logoCanvas = null;
    let logoMaterial = null;
    let logoImage = null;
    let logoAreaMesh = null;

    // Función para redibujar el canvas del logo (EXACTAMENTE igual que frontend)
    function redrawLogoCanvas() {
        if (!logoCanvas || !logoImage || !logoMaterial) return;

        const ctx = logoCanvas.getContext('2d');
        if (!ctx) return;

        const transform = logoTransform || { x: 0, y: 0, scale: 1, rotation: 0 };

        // Limpiar canvas (transparente)
        ctx.clearRect(0, 0, logoCanvas.width, logoCanvas.height);

        // Calcular tamaño base manteniendo aspect ratio de la imagen
        const imgAspect = logoImage.width / logoImage.height;
        const canvasAspect = logoCanvas.width / logoCanvas.height;

        let baseWidth, baseHeight;

        if (imgAspect > canvasAspect) {
            // Imagen más ancha proporcionalmente
            baseWidth = logoCanvas.width * 0.8;
            baseHeight = baseWidth / imgAspect;
        } else {
            // Imagen más alta proporcionalmente
            baseHeight = logoCanvas.height * 0.8;
            baseWidth = baseHeight * imgAspect;
        }

        // Aplicar escala
        const logoWidth = baseWidth * transform.scale;
        const logoHeight = baseHeight * transform.scale;

        // Calcular posición centrada + offset del transform
        const centerX = logoCanvas.width / 2;
        const centerY = logoCanvas.height / 2;
        const x = centerX + transform.x * (logoCanvas.width / 2);
        const y = centerY + transform.y * (logoCanvas.height / 2);

        // Aplicar transformaciones: volteo vertical + rotación (igual que frontend)
        ctx.save();
        ctx.scale(1, -1); // Voltear verticalmente
        ctx.translate(x, -y); // Mover al punto de dibujo (invertido por el flip)

        // Aplicar rotación en grados (convertir a radianes)
        const rotation = (transform.rotation || 0) * Math.PI / 180;
        ctx.rotate(-rotation); // Negativo porque el eje Y está invertido

        // Dibujar la imagen centrada en el punto de rotación
        ctx.drawImage(logoImage, -logoWidth / 2, -logoHeight / 2, logoWidth, logoHeight);
        ctx.restore();

        // Actualizar textura
        if (logoMaterial.map) {
            logoMaterial.map.needsUpdate = true;
        }
    }

    // Aplicar logo al mesh "LogoArea" del modelo (igual que frontend)
    function applyLogoToModel(gltfScene) {
        if (!logoUrl) return;

        // Buscar el mesh LogoArea en el modelo
        let foundMesh = null;
        gltfScene.traverse(function(child) {
            if (child.isMesh && child.name === 'LogoArea') {
                foundMesh = child;
            }
        });

        if (!foundMesh) {
            console.log('No se encontró mesh LogoArea en el modelo');
            return;
        }

        logoAreaMesh = foundMesh;
        logoAreaMesh.visible = true;

        // Obtener aspect ratio del LogoArea desde su bounding box
        const geometry = logoAreaMesh.geometry;
        geometry.computeBoundingBox();
        const bbox = geometry.boundingBox;
        if (!bbox) return;
        const size = bbox.getSize(new THREE.Vector3());

        // El LogoArea puede estar en XY, XZ o YZ - tomamos las dos dimensiones más grandes
        const dims = [size.x, size.y, size.z].sort((a, b) => b - a);
        const meshAspect = dims[0] / dims[1];

        // Crear canvas con el aspect ratio del mesh
        logoCanvas = document.createElement('canvas');
        const baseSize = 1024;
        if (meshAspect >= 1) {
            logoCanvas.width = baseSize;
            logoCanvas.height = Math.round(baseSize / meshAspect);
        } else {
            logoCanvas.width = Math.round(baseSize * meshAspect);
            logoCanvas.height = baseSize;
        }

        // Crear textura con filtrado de alta calidad
        const texture = new THREE.CanvasTexture(logoCanvas);
        texture.colorSpace = THREE.SRGBColorSpace;
        texture.minFilter = THREE.LinearFilter;
        texture.magFilter = THREE.LinearFilter;
        texture.generateMipmaps = false;

        // Material para el LogoArea (igual que frontend)
        logoMaterial = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            side: THREE.DoubleSide,
            depthWrite: false
        });
        logoAreaMesh.material = logoMaterial;

        // Cargar imagen del logo
        logoImage = new Image();
        logoImage.crossOrigin = 'anonymous';
        logoImage.onload = function() {
            redrawLogoCanvas();
        };
        logoImage.onerror = function() {
            console.error('Error al cargar imagen del logo:', logoUrl);
        };
        logoImage.src = logoUrl;
    }

    // Cargar HDRI y aplicarlo como environment map
    async function loadHDRI() {
        try {
            // Obtener URL del HDRI desde el API
            const response = await fetch('/api/v1/settings/hdri');
            if (!response.ok) return null;

            const data = await response.json();
            if (!data?.success || !data?.data?.hdri_url) return null;

            let hdriUrl = data.data.hdri_url;

            // Convertir URL de S3 a proxy local
            if (hdriUrl && hdriUrl.includes('s3.') && hdriUrl.includes('amazonaws.com')) {
                const match = hdriUrl.match(/amazonaws\.com\/(.+)$/);
                if (match) {
                    hdriUrl = '/api/storage/' + match[1];
                }
            }

            return new Promise((resolve, reject) => {
                const rgbeLoader = new RGBELoader();
                rgbeLoader.load(
                    hdriUrl,
                    function(texture) {
                        texture.mapping = THREE.EquirectangularReflectionMapping;

                        // Generar environment map con PMREM
                        pmremGenerator = new THREE.PMREMGenerator(renderer);
                        pmremGenerator.compileEquirectangularShader();
                        envMap = pmremGenerator.fromEquirectangular(texture).texture;

                        // Aplicar a la escena (no como background, igual que frontend)
                        scene.environment = envMap;

                        // Limpiar
                        texture.dispose();
                        pmremGenerator.dispose();

                        console.log('HDRI cargado correctamente');
                        resolve(envMap);
                    },
                    undefined,
                    function(error) {
                        console.warn('No se pudo cargar HDRI:', error);
                        resolve(null);
                    }
                );
            });
        } catch (error) {
            console.warn('Error obteniendo configuración HDRI:', error);
            return null;
        }
    }

    function init() {
        if (initialized) return;

        const container = document.getElementById(containerId);
        if (!container || !modelUrl) {
            const loading = container?.querySelector('.viewer-loading');
            if (loading) {
                loading.innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle"></i> No hay modelo 3D disponible</p>';
            }
            return;
        }

        const width = container.clientWidth;
        const height = container.clientHeight;

        if (width === 0 || height === 0) return;

        initialized = true;

        // Scene con fondo igual al frontend
        scene = new THREE.Scene();
        scene.background = new THREE.Color('#f0f4f8');

        // Camera igual al frontend
        camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        camera.position.set(1.5, 1, 2.5);

        // Renderer con configuración igual al frontend (React Three Fiber defaults)
        renderer = new THREE.WebGLRenderer({
            antialias: true,
            preserveDrawingBuffer: true,
            alpha: false
        });
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1;

        container.innerHTML = '';
        container.appendChild(renderer.domElement);

        // Controls igual al frontend
        controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.rotateSpeed = 0.5;
        controls.zoomSpeed = 0.5;
        controls.panSpeed = 0.5;
        controls.minDistance = 1;
        controls.maxDistance = 10;
        // Auto-rotar solo si no hay logo (igual que frontend)
        controls.autoRotate = !logoUrl;
        controls.autoRotateSpeed = 0.5;

        // Iluminación básica (fallback si no hay HDRI)
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        scene.add(ambientLight);

        const directionalLight1 = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight1.position.set(5, 5, 5);
        directionalLight1.castShadow = true;
        scene.add(directionalLight1);

        const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.4);
        directionalLight2.position.set(-5, 3, -5);
        scene.add(directionalLight2);

        // Cargar HDRI para iluminación realista
        loadHDRI();

        // Cargar modelo
        const loader = new GLTFLoader();
        loader.load(
            modelUrl,
            function(gltf) {
                model = gltf.scene;

                // Escalar y centrar igual al frontend
                const box = new THREE.Box3().setFromObject(model);
                const size = box.getSize(new THREE.Vector3());
                const maxDim = Math.max(size.x, size.y, size.z);
                const scale = 2 / maxDim;
                model.scale.setScalar(scale);

                // Recalcular bounding box después de escalar
                box.setFromObject(model);
                const scaledCenter = box.getCenter(new THREE.Vector3());
                model.position.set(-scaledCenter.x, -scaledCenter.y, -scaledCenter.z);

                // Aplicar color a todos los meshes EXCEPTO LogoArea (igual que frontend)
                const newColor = colorHex ? new THREE.Color(colorHex) : null;
                model.traverse(function(child) {
                    if (child.isMesh && child.name !== 'LogoArea') {
                        if (child.material) {
                            if (newColor && child.material.color) {
                                child.material.color.copy(newColor);
                            }
                            // Si tenemos envMap, aplicarlo al material
                            if (envMap && child.material.envMap !== undefined) {
                                child.material.envMap = envMap;
                                child.material.envMapIntensity = 1;
                            }
                            child.material.needsUpdate = true;
                        }
                    }
                });

                scene.add(model);

                // Aplicar logo al mesh LogoArea
                applyLogoToModel(model);
            },
            undefined,
            function(error) {
                console.error('Error loading model:', error);
                container.innerHTML = '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #dc3545; text-align: center;"><i class="bi bi-exclamation-triangle display-4"></i><p>Error al cargar el modelo</p></div>';
            }
        );

        function animate() {
            requestAnimationFrame(animate);

            if (autoRotate && model) {
                model.rotation.y += 0.005;
            }

            controls.update();
            renderer.render(scene, camera);
        }
        animate();

        const resizeObserver = new ResizeObserver(entries => {
            for (let entry of entries) {
                const newWidth = entry.contentRect.width;
                const newHeight = entry.contentRect.height;
                if (newWidth > 0 && newHeight > 0 && renderer) {
                    camera.aspect = newWidth / newHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(newWidth, newHeight);
                }
            }
        });
        resizeObserver.observe(container);
    }

    function setupButtons() {
        const btnReset = document.getElementById('btn-reset-' + funcId);
        const btnRotate = document.getElementById('btn-rotate-' + funcId);
        const btnDownload = document.getElementById('btn-download-' + funcId);

        if (btnReset) {
            btnReset.addEventListener('click', function() {
                if (camera && controls) {
                    camera.position.set(1.5, 1, 2.5);
                    controls.reset();
                }
            });
        }

        if (btnRotate) {
            btnRotate.addEventListener('click', function() {
                autoRotate = !autoRotate;
                // Desactivar auto-rotate de controls cuando usamos manual
                if (controls) {
                    controls.autoRotate = false;
                }
                this.classList.toggle('btn-outline-secondary', !autoRotate);
                this.classList.toggle('btn-secondary', autoRotate);
            });
        }

        if (btnDownload) {
            btnDownload.addEventListener('click', function() {
                if (renderer && scene && camera) {
                    renderer.render(scene, camera);
                    const dataUrl = renderer.domElement.toDataURL('image/png', 1.0);
                    const link = document.createElement('a');
                    link.download = 'modelo-3d-personalizado.png';
                    link.href = dataUrl;
                    link.click();
                }
            });
        }
    }

    function tryInit() {
        const container = document.getElementById(containerId);
        if (!container) return;

        setupButtons();

        if (container.offsetWidth > 0 && container.offsetHeight > 0) {
            init();
        } else {
            const modal = container.closest('.modal');
            if (modal) {
                modal.addEventListener('shown.bs.modal', function() {
                    setTimeout(init, 200);
                }, { once: true });
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryInit);
    } else {
        tryInit();
    }
})();
</script>
@endpush
