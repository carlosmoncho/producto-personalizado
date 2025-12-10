@extends('layouts.admin')

@section('title', 'Crear Atributo')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.product-attributes.index') }}">Atributos</a>
                    </li>
                    <li class="breadcrumb-item active">Crear Atributo</li>
                </ol>
            </nav>
            <h2>Crear Nuevo Atributo</h2>
            <p class="text-muted">Configura un nuevo atributo para el configurador de productos</p>
        </div>
        <div>
            <a href="{{ route('admin.product-attributes.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.product-attributes.store') }}" id="attributeForm" novalidate>
        @csrf
        <div class="row">
            <!-- Columna principal -->
            <div class="col-lg-8">
                <!-- Información básica -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Información Básica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="attribute_group_id" class="form-label">Grupo de Atributos <span class="text-danger">*</span></label>
                                <select class="form-select @error('attribute_group_id') is-invalid @enderror"
                                        id="attribute_group_id" name="attribute_group_id" required onchange="updateTypeFromGroup()">
                                    <option value="">Selecciona un grupo</option>
                                    @foreach($attributeGroups as $group)
                                        <option value="{{ $group->id }}"
                                                data-type="{{ $group->type }}"
                                                {{ old('attribute_group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} ({{ ucfirst($group->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('attribute_group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>El grupo determina la categoría del atributo.
                                    <a href="{{ route('admin.attribute-groups.create') }}" target="_blank">Crear nuevo grupo</a></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Tipo de Atributo <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type" name="type" required onchange="handleTypeChange()" readonly>
                                    <option value="">Selecciona primero un grupo</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>El tipo se selecciona automáticamente según el grupo</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="ej. Blanco, Algodón, 20x30 cm">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>Nombre que verán los usuarios en el configurador</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="value" class="form-label">Valor Técnico <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('value') is-invalid @enderror" 
                                       id="value" name="value" value="{{ old('value') }}" required
                                       placeholder="ej. BLANCO, ALGODON_100, 20x30">
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>Valor único usado internamente (sin espacios, mayúsculas)</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Orden de Visualización</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                                       min="0" max="9999">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>Orden en el que aparece (0 = primero)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración específica por tipo -->
                <div class="card shadow-sm mb-4" id="typeSpecificConfig" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0" id="typeConfigTitle">
                            <i class="bi bi-gear me-2"></i>Configuración Específica
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Color Configuration -->
                        <div id="colorConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="hex_code" class="form-label">Código de Color <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color"
                                               id="color_picker" onchange="updateHexCode()"
                                               value="{{ old('hex_code', '#FFFFFF') }}" style="width: 60px;">
                                        <input type="text" class="form-control @error('hex_code') is-invalid @enderror"
                                               id="hex_code" name="hex_code" value="{{ old('hex_code', '#FFFFFF') }}"
                                               placeholder="#FFFFFF" pattern="^#[0-9A-Fa-f]{6}$" data-conditional-required>
                                    </div>
                                    @error('hex_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small>Usar selector de color o escribir código hexadecimal</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="color_family" class="form-label">Familia de Color</label>
                                    <select class="form-select" id="color_family" name="color_family">
                                        <option value="">Seleccionar familia</option>
                                        <option value="basicos" {{ old('color_family') == 'basicos' ? 'selected' : '' }}>Básicos</option>
                                        <option value="vibrantes" {{ old('color_family') == 'vibrantes' ? 'selected' : '' }}>Vibrantes</option>
                                        <option value="pasteles" {{ old('color_family') == 'pasteles' ? 'selected' : '' }}>Pasteles</option>
                                        <option value="metalicos" {{ old('color_family') == 'metalicos' ? 'selected' : '' }}>Metálicos</option>
                                        <option value="neon" {{ old('color_family') == 'neon' ? 'selected' : '' }}>Neón</option>
                                    </select>
                                    <div class="form-text">
                                        <small>Ayuda a organizar los colores por categorías</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="finish_type" class="form-label">Tipo de Acabado</label>
                                    <select class="form-select" id="finish_type" name="finish_type">
                                        <option value="mate" {{ old('finish_type') == 'mate' ? 'selected' : '' }}>Mate</option>
                                        <option value="brillante" {{ old('finish_type') == 'brillante' ? 'selected' : '' }}>Brillante</option>
                                        <option value="satinado" {{ old('finish_type') == 'satinado' ? 'selected' : '' }}>Satinado</option>
                                        <option value="metalico" {{ old('finish_type') == 'metalico' ? 'selected' : '' }}>Metálico</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="certifications" class="form-label">Certificaciones</label>
                                    <input type="text" class="form-control" id="certifications" name="certifications" 
                                           value="{{ old('certifications') }}" 
                                           placeholder="ECO, BIODEGRADABLE, RECICLABLE">
                                    <div class="form-text">
                                        <small>Separar con comas</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Material Configuration -->
                        <div id="materialConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="material_type" class="form-label">Tipo de Material</label>
                                    <select class="form-select" id="material_type" name="material_type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="papel" {{ old('material_type') == 'papel' ? 'selected' : '' }}>Papel</option>
                                        <option value="carton" {{ old('material_type') == 'carton' ? 'selected' : '' }}>Cartón</option>
                                        <option value="plastico" {{ old('material_type') == 'plastico' ? 'selected' : '' }}>Plástico</option>
                                        <option value="tela" {{ old('material_type') == 'tela' ? 'selected' : '' }}>Tela</option>
                                        <option value="metal" {{ old('material_type') == 'metal' ? 'selected' : '' }}>Metal</option>
                                        <option value="madera" {{ old('material_type') == 'madera' ? 'selected' : '' }}>Madera</option>
                                        <option value="vidrio" {{ old('material_type') == 'vidrio' ? 'selected' : '' }}>Vidrio</option>
                                        <option value="ceramica" {{ old('material_type') == 'ceramica' ? 'selected' : '' }}>Cerámica</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="thickness" class="form-label">Grosor/Gramaje</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="thickness" name="thickness" 
                                               value="{{ old('thickness') }}" step="0.1" min="0">
                                        <select class="form-select" id="thickness_unit" name="thickness_unit" style="max-width: 80px;">
                                            <option value="mm" {{ old('thickness_unit') == 'mm' ? 'selected' : '' }}>mm</option>
                                            <option value="gsm" {{ old('thickness_unit') == 'gsm' ? 'selected' : '' }}>g/m²</option>
                                            <option value="mic" {{ old('thickness_unit') == 'mic' ? 'selected' : '' }}>μm</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="surface_finish" class="form-label">Acabado Superficial</label>
                                    <select class="form-select" id="surface_finish" name="surface_finish">
                                        <option value="liso" {{ old('surface_finish') == 'liso' ? 'selected' : '' }}>Liso</option>
                                        <option value="texturizado" {{ old('surface_finish') == 'texturizado' ? 'selected' : '' }}>Texturizado</option>
                                        <option value="rugoso" {{ old('surface_finish') == 'rugoso' ? 'selected' : '' }}>Rugoso</option>
                                        <option value="brillante" {{ old('surface_finish') == 'brillante' ? 'selected' : '' }}>Brillante</option>
                                        <option value="mate" {{ old('surface_finish') == 'mate' ? 'selected' : '' }}>Mate</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="material_certifications" class="form-label">Certificaciones</label>
                                    <input type="text" class="form-control" id="material_certifications" name="certifications" 
                                           value="{{ old('certifications') }}" 
                                           placeholder="FSC, PEFC, RECICLABLE, BIODEGRADABLE">
                                    <div class="form-text">
                                        <small>Separar con comas</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ink Configuration -->
                        <div id="inkConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ink_hex_code" class="form-label">Color de la Tinta <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color"
                                               id="ink_color_picker" onchange="updateInkHexCode()" style="width: 60px;">
                                        <input type="text" class="form-control"
                                               id="ink_hex_code" name="hex_code" value="{{ old('hex_code') }}"
                                               placeholder="#000000" data-conditional-required>
                                    </div>
                                    <div class="form-text">
                                        <small>Color exacto de la tinta de impresión</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ink_type" class="form-label">Tipo de Tinta</label>
                                    <select class="form-select" id="ink_type" name="ink_type">
                                        <option value="agua" {{ old('ink_type') == 'agua' ? 'selected' : '' }}>Base Agua</option>
                                        <option value="solvente" {{ old('ink_type') == 'solvente' ? 'selected' : '' }}>Base Solvente</option>
                                        <option value="uv" {{ old('ink_type') == 'uv' ? 'selected' : '' }}>UV</option>
                                        <option value="latex" {{ old('ink_type') == 'latex' ? 'selected' : '' }}>Látex</option>
                                        <option value="sublimacion" {{ old('ink_type') == 'sublimacion' ? 'selected' : '' }}>Sublimación</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="opacity" class="form-label">Opacidad</label>
                                    <select class="form-select" id="opacity" name="opacity">
                                        <option value="transparente" {{ old('opacity') == 'transparente' ? 'selected' : '' }}>Transparente</option>
                                        <option value="baja" {{ old('opacity') == 'baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="media" {{ old('opacity', 'media') == 'media' ? 'selected' : '' }}>Media</option>
                                        <option value="alta" {{ old('opacity') == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="opaca" {{ old('opacity') == 'opaca' ? 'selected' : '' }}>Opaca</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="durability" class="form-label">Durabilidad</label>
                                    <select class="form-select" id="durability" name="durability">
                                        <option value="temporal" {{ old('durability') == 'temporal' ? 'selected' : '' }}>Temporal (1-6 meses)</option>
                                        <option value="media" {{ old('durability') == 'media' ? 'selected' : '' }}>Media (6-24 meses)</option>
                                        <option value="larga" {{ old('durability') == 'larga' ? 'selected' : '' }}>Larga (2-5 años)</option>
                                        <option value="permanente" {{ old('durability') == 'permanente' ? 'selected' : '' }}>Permanente (5+ años)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label d-block">Características</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_metallic" name="is_metallic" value="1" 
                                               {{ old('is_metallic') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_metallic">
                                            Metálica
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_fluorescent" name="is_fluorescent" value="1" 
                                               {{ old('is_fluorescent') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_fluorescent">
                                            Fluorescente
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ink Color Configuration -->
                        <div id="ink_colorConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ink_color_hex_code" class="form-label">Color de Tinta <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color"
                                               id="ink_color_color_picker" onchange="updateInkColorHexCode()" style="width: 60px;"
                                               value="{{ old('hex_code', '#000000') }}">
                                        <input type="text" class="form-control"
                                               id="ink_color_hex_code" name="hex_code" value="{{ old('hex_code') }}"
                                               placeholder="#000000" data-conditional-required>
                                    </div>
                                    <div class="form-text">
                                        <small>Selecciona el color de la tinta</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ink_color_family" class="form-label">Familia de Color</label>
                                    <select class="form-select" id="ink_color_family" name="color_family">
                                        <option value="">Seleccionar familia</option>
                                        <option value="basicos" {{ old('color_family') == 'basicos' ? 'selected' : '' }}>Básicos</option>
                                        <option value="vibrantes" {{ old('color_family') == 'vibrantes' ? 'selected' : '' }}>Vibrantes</option>
                                        <option value="pasteles" {{ old('color_family') == 'pasteles' ? 'selected' : '' }}>Pasteles</option>
                                        <option value="metalicos" {{ old('color_family') == 'metalicos' ? 'selected' : '' }}>Metálicos</option>
                                        <option value="neon" {{ old('color_family') == 'neon' ? 'selected' : '' }}>Neón</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ink_color_finish" class="form-label">Tipo de Acabado</label>
                                    <select class="form-select" id="ink_color_finish" name="finish_type">
                                        <option value="mate" {{ old('finish_type') == 'mate' ? 'selected' : '' }}>Mate</option>
                                        <option value="brillante" {{ old('finish_type') == 'brillante' ? 'selected' : '' }}>Brillante</option>
                                        <option value="satinado" {{ old('finish_type') == 'satinado' ? 'selected' : '' }}>Satinado</option>
                                        <option value="metalico" {{ old('finish_type') == 'metalico' ? 'selected' : '' }}>Metálico</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">Características</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="ink_color_is_metallic" name="is_metallic" value="1"
                                               {{ old('is_metallic') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ink_color_is_metallic">
                                            Metálica
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="ink_color_is_fluorescent" name="is_fluorescent" value="1"
                                               {{ old('is_fluorescent') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ink_color_is_fluorescent">
                                            Fluorescente
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Size Configuration -->
                        <div id="sizeConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="width" class="form-label">Ancho</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="width" name="width" 
                                               value="{{ old('width') }}" step="0.1" min="0">
                                        <select class="form-select" id="width_unit" name="width_unit" style="max-width: 80px;">
                                            <option value="mm" {{ old('width_unit', 'mm') == 'mm' ? 'selected' : '' }}>mm</option>
                                            <option value="cm" {{ old('width_unit') == 'cm' ? 'selected' : '' }}>cm</option>
                                            <option value="m" {{ old('width_unit') == 'm' ? 'selected' : '' }}>m</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="height" class="form-label">Alto</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="height" name="height" 
                                               value="{{ old('height') }}" step="0.1" min="0">
                                        <select class="form-select" id="height_unit" name="height_unit" style="max-width: 80px;">
                                            <option value="mm" {{ old('height_unit', 'mm') == 'mm' ? 'selected' : '' }}>mm</option>
                                            <option value="cm" {{ old('height_unit') == 'cm' ? 'selected' : '' }}>cm</option>
                                            <option value="m" {{ old('height_unit') == 'm' ? 'selected' : '' }}>m</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="depth" class="form-label">Profundidad (opcional)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="depth" name="depth" 
                                               value="{{ old('depth') }}" step="0.1" min="0">
                                        <select class="form-select" id="depth_unit" name="depth_unit" style="max-width: 80px;">
                                            <option value="mm" {{ old('depth_unit', 'mm') == 'mm' ? 'selected' : '' }}>mm</option>
                                            <option value="cm" {{ old('depth_unit') == 'cm' ? 'selected' : '' }}>cm</option>
                                            <option value="m" {{ old('depth_unit') == 'm' ? 'selected' : '' }}>m</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="size_category" class="form-label">Categoría</label>
                                    <select class="form-select" id="size_category" name="size_category">
                                        <option value="pequeno" {{ old('size_category') == 'pequeno' ? 'selected' : '' }}>Pequeño</option>
                                        <option value="mediano" {{ old('size_category') == 'mediano' ? 'selected' : '' }}>Mediano</option>
                                        <option value="grande" {{ old('size_category') == 'grande' ? 'selected' : '' }}>Grande</option>
                                        <option value="extra_grande" {{ old('size_category') == 'extra_grande' ? 'selected' : '' }}>Extra Grande</option>
                                        <option value="personalizado" {{ old('size_category') == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="print_area" class="form-label">Área de Impresión (%)</label>
                                    <input type="number" class="form-control" id="print_area" name="print_area" 
                                           value="{{ old('print_area', 80) }}" min="10" max="100">
                                    <div class="form-text">
                                        <small>Porcentaje del área total que se puede imprimir</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Configuration -->
                        <div id="quantityConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_value" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="quantity_value" name="quantity_value"
                                           value="{{ old('quantity_value') }}" min="1" data-conditional-required>
                                    <div class="form-text">
                                        <small>Número exacto de unidades</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="packaging" class="form-label">Empaquetado</label>
                                    <input type="text" class="form-control" id="packaging" name="packaging" 
                                           value="{{ old('packaging') }}" 
                                           placeholder="ej. 54 CAJAS de 300 unid.">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="unit_price" class="form-label">Precio Unitario</label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                               value="{{ old('unit_price') }}" step="0.001" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="min_quantity" class="form-label">Cantidad Mínima</label>
                                    <input type="number" class="form-control" id="min_quantity" name="min_quantity" 
                                           value="{{ old('min_quantity', 1) }}" min="1">
                                    <div class="form-text">
                                        <small>Cantidad mínima para este nivel de precio</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cliché Configuration -->
                        <div id="clicheConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cliche_type" class="form-label">Tipo de Cliché</label>
                                    <select class="form-select" id="cliche_type" name="cliche_type">
                                        <option value="standard" {{ old('cliche_type') == 'standard' ? 'selected' : '' }}>Estándar</option>
                                        <option value="reducido" {{ old('cliche_type') == 'reducido' ? 'selected' : '' }}>Reducido</option>
                                        <option value="orla" {{ old('cliche_type') == 'orla' ? 'selected' : '' }}>Orla (Gratuito)</option>
                                    </select>
                                    <div class="form-text">
                                        <small>Tipo de cliché para la impresión</small>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Nota:</strong> El precio del cliché se configura mediante dependencias.
                            </div>
                        </div>

                        <!-- System Configuration -->
                        <div id="systemConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="system_type" class="form-label">Tipo de Sistema</label>
                                    <select class="form-select" id="system_type" name="system_type">
                                        <option value="offset" {{ old('system_type') == 'offset' ? 'selected' : '' }}>Offset</option>
                                        <option value="digital" {{ old('system_type') == 'digital' ? 'selected' : '' }}>Digital</option>
                                        <option value="serigrafia" {{ old('system_type') == 'serigrafia' ? 'selected' : '' }}>Serigrafía</option>
                                        <option value="flexografia" {{ old('system_type') == 'flexografia' ? 'selected' : '' }}>Flexografía</option>
                                        <option value="tampografia" {{ old('system_type') == 'tampografia' ? 'selected' : '' }}>Tampografía</option>
                                        <option value="sublimacion" {{ old('system_type') == 'sublimacion' ? 'selected' : '' }}>Sublimación</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="max_colors" class="form-label">Máximo de Colores</label>
                                    <input type="number" class="form-control" id="max_colors" name="max_colors" 
                                           value="{{ old('max_colors', 4) }}" min="1" max="12">
                                    <div class="form-text">
                                        <small>Número máximo de colores que soporta</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="resolution" class="form-label">Resolución (DPI)</label>
                                    <select class="form-select" id="resolution" name="resolution">
                                        <option value="300" {{ old('resolution') == '300' ? 'selected' : '' }}>300 DPI</option>
                                        <option value="600" {{ old('resolution') == '600' ? 'selected' : '' }}>600 DPI</option>
                                        <option value="1200" {{ old('resolution') == '1200' ? 'selected' : '' }}>1200 DPI</option>
                                        <option value="2400" {{ old('resolution') == '2400' ? 'selected' : '' }}>2400 DPI</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="production_speed" class="form-label">Velocidad de Producción</label>
                                    <select class="form-select" id="production_speed" name="production_speed">
                                        <option value="lenta" {{ old('production_speed') == 'lenta' ? 'selected' : '' }}>Lenta (+5 días)</option>
                                        <option value="normal" {{ old('production_speed') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="rapida" {{ old('production_speed') == 'rapida' ? 'selected' : '' }}>Rápida (-2 días)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna lateral -->
            <div class="col-lg-4">
                <!-- Vista previa -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-eye me-2"></i>Vista Previa
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="preview" class="mb-3">
                            <div class="text-muted">
                                <i class="bi bi-image display-4"></i>
                                <p class="mt-2">Selecciona un tipo para ver la vista previa</p>
                            </div>
                        </div>
                        <div id="previewDetails" class="text-start" style="display: none;">
                            <small class="text-muted">
                                <strong>Detalles:</strong><br>
                                <span id="previewType">-</span><br>
                                <span id="previewValue">-</span><br>
                                <span id="previewPrice">-</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Configuración de estado -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-toggle-on me-2"></i>Estado y Opciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                   {{ old('active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                <strong>Activo</strong>
                                <div class="form-text">El atributo estará disponible para usar</div>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_recommended" name="is_recommended" value="1" 
                                   {{ old('is_recommended') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_recommended">
                                <strong>Recomendado</strong>
                                <div class="form-text">Se destacará como opción recomendada</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Ayuda -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-question-circle me-2"></i>Ayuda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="typeHelp">
                            <p class="small text-muted mb-2">
                                <strong>Consejos:</strong>
                            </p>
                            <ul class="small text-muted mb-0" style="padding-left: 1rem;">
                                <li>Usa nombres descriptivos y claros</li>
                                <li>El valor técnico debe ser único por tipo</li>
                                <li>Los modificadores de precio se aplicarán automáticamente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.product-attributes.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="previewAttribute()">
                                    <i class="bi bi-eye me-2"></i>Vista Previa
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Crear Atributo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateTypeFromGroup() {
    const groupSelect = document.getElementById('attribute_group_id');
    const typeSelect = document.getElementById('type');
    const selectedOption = groupSelect.options[groupSelect.selectedIndex];

    if (selectedOption && selectedOption.dataset.type) {
        const groupType = selectedOption.dataset.type;
        typeSelect.value = groupType;
        typeSelect.disabled = true; // Desactivar para que no se pueda cambiar
        handleTypeChange();
    } else {
        typeSelect.value = '';
        typeSelect.disabled = false;
    }
}

function handleTypeChange() {
    const type = document.getElementById('type').value;
    const typeSpecificConfig = document.getElementById('typeSpecificConfig');
    const typeConfigs = document.querySelectorAll('.type-config');
    const preview = document.getElementById('preview');
    const previewDetails = document.getElementById('previewDetails');

    // Reset required attributes for all fields
    document.querySelectorAll('input[data-conditional-required]').forEach(input => {
        input.removeAttribute('required');
    });

    // Hide all type configs and disable their inputs
    typeConfigs.forEach(config => {
        config.style.display = 'none';
        config.querySelectorAll('input, select, textarea').forEach(input => {
            input.disabled = true;
        });
    });

    if (type) {
        // Show type specific config
        typeSpecificConfig.style.display = 'block';

        // Show relevant config
        const configElement = document.getElementById(type + 'Config');
        if (configElement) {
            configElement.style.display = 'block';

            // Enable all inputs in the visible section
            configElement.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = false;
            });

            // Set required attributes for visible fields
            configElement.querySelectorAll('input[data-conditional-required]').forEach(input => {
                input.setAttribute('required', 'required');
            });
        }

        // Update title and set specific defaults
        const typeNames = {
            'color': 'Color',
            'material': 'Material',
            'size': 'Tamaño',
            'ink': 'Tinta de Impresión',
            'ink_color': 'Color de Tinta',
            'cliche': 'Cliché',
            'quantity': 'Cantidad',
            'system': 'Sistema de Impresión'
        };

        document.getElementById('typeConfigTitle').innerHTML =
            `<i class="bi bi-gear me-2"></i>Configuración de ${typeNames[type]}`;

        // Set type-specific defaults
        setTypeDefaults(type);

        // Update preview
        updatePreview();
        previewDetails.style.display = 'block';
    } else {
        typeSpecificConfig.style.display = 'none';
        previewDetails.style.display = 'none';
        preview.innerHTML = `
            <div class="text-muted">
                <i class="bi bi-image display-4"></i>
                <p class="mt-2">Selecciona un tipo para ver la vista previa</p>
            </div>
        `;
    }
}

function setTypeDefaults(type) {
    const nameField = document.getElementById('name');
    const valueField = document.getElementById('value');
    
    // Clear previous values
    if (!nameField.value) {
        switch(type) {
            case 'color':
                nameField.placeholder = 'ej. Rojo Ferrari, Azul Marino, Verde Menta';
                valueField.placeholder = 'ej. ROJO_FERRARI, AZUL_MARINO, VERDE_MENTA';
                break;
            case 'material':
                nameField.placeholder = 'ej. Papel Couché 115g, Cartón Reciclado, PVC Blanco';
                valueField.placeholder = 'ej. PAPEL_COUCHE_115G, CARTON_RECICLADO, PVC_BLANCO';
                break;
            case 'size':
                nameField.placeholder = 'ej. A4 (21x29.7cm), Tarjeta Visita, Banner 3x2m';
                valueField.placeholder = 'ej. A4_21X297, TARJETA_VISITA, BANNER_3X2';
                break;
            case 'ink':
                nameField.placeholder = 'ej. Negro Intenso, Dorado Metálico, Blanco Opaco';
                valueField.placeholder = 'ej. NEGRO_INTENSO, DORADO_METALICO, BLANCO_OPACO';
                break;
            case 'ink_color':
                nameField.placeholder = 'ej. Rojo, Azul, Verde, Dorado';
                valueField.placeholder = 'ej. ROJO, AZUL, VERDE, DORADO';
                break;
            case 'cliche':
                nameField.placeholder = 'ej. Cliché Estándar, Cliché Reducido, Orla';
                valueField.placeholder = 'ej. CLICHE_STANDARD, CLICHE_REDUCIDO, ORLA';
                break;
            case 'quantity':
                nameField.placeholder = 'ej. 1,000 unidades, 5,000 unidades, 50,000 unidades';
                valueField.placeholder = 'ej. QTY_1000, QTY_5000, QTY_50000';
                break;
            case 'system':
                nameField.placeholder = 'ej. Offset 4 Colores, Digital HP Indigo, Serigrafía UV';
                valueField.placeholder = 'ej. OFFSET_4C, DIGITAL_HP, SERIGRAFIA_UV';
                break;
        }
    }
}

function updateHexCode() {
    const colorPicker = document.getElementById('color_picker');
    const hexCode = document.getElementById('hex_code');
    if (colorPicker && hexCode) {
        hexCode.value = colorPicker.value.toUpperCase();
        updatePreview();
    }
}

// Sincronizar hex_code con color_picker cuando se escribe manualmente
document.addEventListener('DOMContentLoaded', function() {
    const hexCodeInput = document.getElementById('hex_code');
    const colorPicker = document.getElementById('color_picker');

    if (hexCodeInput && colorPicker) {
        // Sincronizar al cargar si hay un valor inicial
        if (hexCodeInput.value && /^#[0-9A-Fa-f]{6}$/.test(hexCodeInput.value)) {
            colorPicker.value = hexCodeInput.value;
        }

        // Sincronizar cuando se escribe manualmente
        hexCodeInput.addEventListener('input', function() {
            const value = this.value;
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                colorPicker.value = value;
                updatePreview();
            }
        });
    }

    // Mismo proceso para ink
    const inkHexCodeInput = document.getElementById('ink_hex_code');
    const inkColorPicker = document.getElementById('ink_color_picker');

    if (inkHexCodeInput && inkColorPicker) {
        // Sincronizar al cargar si hay un valor inicial
        if (inkHexCodeInput.value && /^#[0-9A-Fa-f]{6}$/.test(inkHexCodeInput.value)) {
            inkColorPicker.value = inkHexCodeInput.value;
        }

        // Sincronizar cuando se escribe manualmente
        inkHexCodeInput.addEventListener('input', function() {
            const value = this.value;
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                inkColorPicker.value = value;
                updatePreview();
            }
        });
    }
});

function updateInkHexCode() {
    const colorPicker = document.getElementById('ink_color_picker');
    const hexCode = document.getElementById('ink_hex_code');
    hexCode.value = colorPicker.value;
    updatePreview();
}

function updateInkColorHexCode() {
    const colorPicker = document.getElementById('ink_color_color_picker');
    const hexCode = document.getElementById('ink_color_hex_code');
    hexCode.value = colorPicker.value.toUpperCase();
    updatePreview();
}

function updatePreview() {
    const type = document.getElementById('type').value;
    const name = document.getElementById('name').value || 'Nuevo Atributo';
    const value = document.getElementById('value').value || 'NUEVO_VALOR';
    
    const preview = document.getElementById('preview');
    let previewHtml = '';
    
    switch(type) {
        case 'color':
            const hexCode = document.getElementById('hex_code').value || '#CCCCCC';
            previewHtml = `
                <div class="color-preview mx-auto mb-2" 
                     style="width: 80px; height: 80px; background-color: ${hexCode}; 
                            border-radius: 12px; border: 3px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value}</small>
            `;
            break;
            
        case 'ink':
            const inkHexCode = document.getElementById('ink_hex_code').value || '#000000';
            previewHtml = `
                <div class="ink-preview mx-auto mb-2"
                     style="width: 60px; height: 60px; background-color: ${inkHexCode};
                            border-radius: 8px; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value}</small>
            `;
            break;

        case 'ink_color':
            const inkColorHexCode = document.getElementById('ink_color_hex_code').value || '#000000';
            previewHtml = `
                <div class="color-preview mx-auto mb-2"
                     style="width: 80px; height: 80px; background-color: ${inkColorHexCode};
                            border-radius: 12px; border: 3px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value}</small>
            `;
            break;

        case 'material':
            previewHtml = `
                <div class="text-success mb-2">
                    <i class="bi bi-layers display-4"></i>
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value}</small>
            `;
            break;
            
        case 'size':
            previewHtml = `
                <div class="text-warning mb-2">
                    <i class="bi bi-rulers display-4"></i>
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value}</small>
            `;
            break;
            
        case 'quantity':
            previewHtml = `
                <div class="text-secondary mb-2">
                    <i class="bi bi-boxes display-4"></i>
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value} unidades</small>
            `;
            break;
            
        case 'cliche':
            previewHtml = `
                <div class="text-info mb-2">
                    <i class="bi bi-stamp display-4"></i>
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value}</small>
            `;
            break;

        case 'system':
            previewHtml = `
                <div class="text-dark mb-2">
                    <i class="bi bi-gear display-4"></i>
                </div>
                <h6 class="mb-1">${name}</h6>
                <small class="text-muted">${value}</small>
            `;
            break;

        default:
            previewHtml = `
                <div class="text-muted">
                    <i class="bi bi-circle display-4"></i>
                    <p class="mt-2">${name}</p>
                </div>
            `;
    }
    
    preview.innerHTML = previewHtml;
    
    // Update preview details
    document.getElementById('previewType').textContent = type ? 
        document.querySelector(`option[value="${type}"]`).textContent : '-';
    document.getElementById('previewValue').textContent = value;
    
    let priceText = 'Sin impacto';
    if (priceModifier !== 0) {
        priceText = (priceModifier > 0 ? '+' : '') + '€' + priceModifier.toFixed(3);
    }
    if (pricePercentage !== 0) {
        priceText += (priceModifier !== 0 ? ' y ' : '') + 
                    (pricePercentage > 0 ? '+' : '') + pricePercentage + '%';
    }
    document.getElementById('previewPrice').textContent = priceText;
}

function previewAttribute() {
    updatePreview();
    // You could also open a modal with a larger preview
}

// Auto-generate value from name
document.getElementById('name').addEventListener('input', function() {
    const value = this.value
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '');
    
    document.getElementById('value').value = value;
    updatePreview();
});

// Update preview on input changes
document.addEventListener('DOMContentLoaded', function() {
    const inputs = ['name', 'value', 'hex_code', 'ink_hex_code', 'ink_color_hex_code'];
    
    inputs.forEach(inputId => {
        const element = document.getElementById(inputId);
        if (element) {
            element.addEventListener('input', updatePreview);
        }
    });
});

// Form validation
document.getElementById('attributeForm').addEventListener('submit', function(e) {
    // Deshabilitar todos los inputs de las secciones ocultas para que no se envíen
    document.querySelectorAll('.type-config').forEach(section => {
        if (section.style.display === 'none') {
            section.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = true;
            });
        } else {
            // Asegurar que los inputs de la sección visible estén habilitados
            section.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = false;
            });
        }
    });

    // Enable the type select before submit so its value gets sent
    const typeSelect = document.getElementById('type');
    typeSelect.disabled = false;

    const type = typeSelect.value;

    // Dynamic validation based on type
    if (type === 'color') {
        const hexCode = document.getElementById('hex_code').value;
        if (!hexCode || !hexCode.match(/^#[0-9A-Fa-f]{6}$/)) {
            e.preventDefault();
            typeSelect.disabled = true; // Re-disable if validation fails
            alert('El código de color es requerido y debe tener formato #RRGGBB');
            return;
        }
    }

    if (type === 'ink') {
        const inkHexCode = document.getElementById('ink_hex_code').value;
        if (!inkHexCode || !inkHexCode.match(/^#[0-9A-Fa-f]{6}$/)) {
            e.preventDefault();
            typeSelect.disabled = true; // Re-disable if validation fails
            alert('El color de la tinta es requerido y debe tener formato #RRGGBB');
            return;
        }
    }

    if (type === 'ink_color') {
        const inkColorHexCode = document.getElementById('ink_color_hex_code').value;
        if (!inkColorHexCode || !inkColorHexCode.match(/^#[0-9A-Fa-f]{6}$/)) {
            e.preventDefault();
            typeSelect.disabled = true; // Re-disable if validation fails
            alert('El color de tinta es requerido y debe tener formato #RRGGBB');
            return;
        }
    }

    if (type === 'quantity') {
        const quantityValue = document.getElementById('quantity_value').value;
        if (!quantityValue || parseInt(quantityValue) < 1) {
            e.preventDefault();
            typeSelect.disabled = true; // Re-disable if validation fails
            alert('La cantidad es requerida y debe ser mayor a 0');
            return;
        }
    }

    // Value uniqueness (basic client-side check)
    const value = document.getElementById('value').value;
    if (!value || value.includes(' ')) {
        e.preventDefault();
        typeSelect.disabled = true; // Re-disable if validation fails
        alert('El valor técnico es requerido y no puede contener espacios');
        return;
    }
});
</script>
@endpush

@push('styles')
<style>
.color-preview, .ink-preview {
    transition: all 0.3s ease;
}

.color-preview:hover, .ink-preview:hover {
    transform: scale(1.05);
}

.form-control-color {
    border: 1px solid #ced4da;
}

.type-config {
    padding: 0;
}

.card-body .form-text {
    margin-top: 0.25rem;
}

#preview {
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.icon-square {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush