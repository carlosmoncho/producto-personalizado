{{--
    Visor de modelo 3D interactivo (Réplica exacta del frontend)
--}}

@php
    $viewerId = $viewerId ?? 'three-viewer-' . uniqid();
    $funcId = str_replace('-', '_', $viewerId);
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
import * as THREE from 'https://esm.sh/three@0.181.0';
import { GLTFLoader } from 'https://esm.sh/three@0.181.0/examples/jsm/loaders/GLTFLoader.js';
import { OrbitControls } from 'https://esm.sh/three@0.181.0/examples/jsm/controls/OrbitControls.js';

(function() {
    const containerId = '{{ $viewerId }}';
    const funcId = '{{ $funcId }}';
    const modelUrl = @json($modelUrl ?? '');
    const colorHex = @json($colorHex ?? '#FFFFFF');
    const logoUrl = @json($logoUrl ?? null);
    const logoTransform = @json($logoTransform ?? ['x' => 0, 'y' => 0, 'scale' => 1]);

    // Habilitar gestión de color igual que React Three Fiber
    THREE.ColorManagement.enabled = true;

    let scene, camera, renderer, controls, model;
    let autoRotate = false;
    let logoPlane = null;
    let logoCanvas = null;
    let logoMaterial = null;
    let logoImage = null;
    let initialized = false;

    function redrawLogoCanvas() {
        if (!logoCanvas || !logoImage || !logoMaterial) return;

        const ctx = logoCanvas.getContext('2d');
        if (!ctx) return;

        const transform = logoTransform || { x: 0, y: 0, scale: 1 };

        ctx.clearRect(0, 0, logoCanvas.width, logoCanvas.height);

        const baseLogoSize = Math.min(logoCanvas.width, logoCanvas.height) * 0.4;
        const aspectRatio = logoImage.width / logoImage.height;
        let logoWidth = aspectRatio > 1 ? baseLogoSize * aspectRatio : baseLogoSize;
        let logoHeight = aspectRatio > 1 ? baseLogoSize : baseLogoSize / aspectRatio;

        logoWidth *= transform.scale || 1;
        logoHeight *= transform.scale || 1;

        let x = (logoCanvas.width - logoWidth) / 2 + (transform.x || 0) * (logoCanvas.width - logoWidth) / 2;
        let y = (logoCanvas.height - logoHeight) / 2 + (transform.y || 0) * (logoCanvas.height - logoHeight) / 2;

        ctx.drawImage(logoImage, x, y, logoWidth, logoHeight);

        if (logoMaterial.map) {
            logoMaterial.map.needsUpdate = true;
        }
    }

    function createLogoPlane(targetMesh) {
        if (!logoUrl || !targetMesh) return;

        const geometry = targetMesh.geometry;
        geometry.computeBoundingBox();
        const bbox = geometry.boundingBox;
        const localSize = bbox.getSize(new THREE.Vector3());

        logoCanvas = document.createElement('canvas');
        logoCanvas.width = 1024;
        logoCanvas.height = 717;

        const texture = new THREE.CanvasTexture(logoCanvas);
        texture.colorSpace = THREE.SRGBColorSpace;

        logoMaterial = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            side: THREE.FrontSide,
            depthWrite: false
        });

        const planeGeom = new THREE.PlaneGeometry(localSize.x * 0.95, localSize.z * 0.95);
        logoPlane = new THREE.Mesh(planeGeom, logoMaterial);
        logoPlane.name = 'LogoPlane';

        logoPlane.position.set(0, bbox.max.y + 0.001, 0);
        logoPlane.rotation.x = -Math.PI / 2;

        targetMesh.add(logoPlane);

        logoImage = new Image();
        logoImage.crossOrigin = 'anonymous';
        logoImage.onload = function() {
            redrawLogoCanvas();
        };
        logoImage.src = logoUrl;
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
        // React Three Fiber v9 usa ACESFilmicToneMapping por defecto
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

        // Iluminación igual al frontend (aunque MeshBasicMaterial no la usa)
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);

        const directionalLight1 = new THREE.DirectionalLight(0xffffff, 1.5);
        directionalLight1.position.set(10, 10, 5);
        scene.add(directionalLight1);

        const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight2.position.set(-5, 5, -5);
        scene.add(directionalLight2);

        const hemisphereLight = new THREE.HemisphereLight(0xffffff, 0x444444, 0.6);
        scene.add(hemisphereLight);

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

                box.setFromObject(model);
                const scaledCenter = box.getCenter(new THREE.Vector3());
                model.position.set(-scaledCenter.x, -scaledCenter.y, -scaledCenter.z);

                // Aplicar material EXACTAMENTE igual al frontend
                let targetMesh = null;
                model.traverse(function(child) {
                    if (child.isMesh) {
                        // Crear color igual que en frontend
                        const color = new THREE.Color(colorHex || '#FFFFFF');

                        const mat = new THREE.MeshBasicMaterial({
                            color: color,
                            side: THREE.DoubleSide
                        });
                        child.material = mat;

                        if (!targetMesh) {
                            targetMesh = child;
                        }
                    }
                });

                scene.add(model);

                if (logoUrl && targetMesh) {
                    createLogoPlane(targetMesh);
                }
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
