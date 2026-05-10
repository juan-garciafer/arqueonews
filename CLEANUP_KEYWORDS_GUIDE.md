# 🧹 Guía de Limpieza y Filtrado de Keywords

## Problema Identificado
Tienes registros inútiles en la BD de keywords como:
- **Q3329789** - Entidades raras de Wikidata
- **HMCS Onondaga (S73)** - Barcos militares
- Otros tipos de vehículos, naves, eventos, personas, etc.
- Falta de enfoque en **lugares históricos y ciudades relevantes**

## Solución Implementada

Se han mejorado significativamente los filtros para enfocarse en **lugares históricos y ciudades**, excluyendo sistemáticamente entidades no geográficas.

### 1. **Limpiar Blacklist Existente** 
Gestiona una lista negra de IDs de Wikidata inútiles.

```bash
# Ver lista negra actual
php artisan cleanup:blacklist-wikidata --list

# Agregar un ID a la lista negra (ej: Q3329789)
php artisan cleanup:blacklist-wikidata --add=Q3329789

# Remover un ID de la lista negra
php artisan cleanup:blacklist-wikidata --remove=Q3329789

# Eliminar todos los keywords en la lista negra de la BD
php artisan cleanup:blacklist-wikidata --clean
```

### 2. **Limpiar Keywords y Aliases**
Elimina registros duplicados, vacíos, sin país, y entidades no geográficas.

```bash
# Análisis previo (pregunta antes de eliminar)
php artisan cleanup:keywords-aliases

# Modo DRY-RUN (simula sin eliminar realmente)
php artisan cleanup:keywords-aliases --dry-run
```

**Elimina:**
- ✅ Keywords sin país asignado
- ✅ Aliases muy cortos (< 3 caracteres)
- ✅ Aliases vacíos o solo espacios
- ✅ Keywords duplicadas (mantiene la más antigua)
- ✅ Keywords sospechosos (barcos, vehículos, personas, eventos, etc.)

### 3. **Importar Keywords desde Wikidata**
Ahora filtra automáticamente enfocándose en **lugares históricos y ciudades**.

```bash
# Importar normalmente (incluye limpieza al final)
php artisan import:wikidata-keywords

# Importar sin ejecutar limpieza después
php artisan import:wikidata-keywords --skip-cleanup
```

**Mejoras en Filtrado:**

#### Query SPARQL Mejorada
- ❌ **Excluye tipos específicos:** barcos de guerra, modelos de vehículos, artefactos, taxonomía
- ✅ **Requiere coordenadas geográficas** (P625) para validar que son lugares reales
- ✅ **Para arqueológicos:** solo lugares con localización definida
- ✅ **Para ciudades:** requiere coordenadas y/o ID de Geonames
- ✅ **Para pueblos:** solo lugares geolocalizables

#### Filtros por Patrón de Nombre
Excluye automáticamente entidades como:
- ❌ Barcos militares (HMCS, HMS, USS)
- ❌ Submarinos, cruceros, destructores
- ❌ Códigos militares o navales `(S73)`, `(D73)`
- ❌ Taxonomía (orden, género, especie)
- ❌ Eventos históricos (batalas, guerras)
- ❌ Personas (apellidos, héroes, figuras)
- ❌ Obras de ficción (películas, libros, novelas)
- ❌ Empresas, marcas y corporaciones
- ❌ Modelos y clases genéricas

---

## 🚀 Workflow Recomendado

### **Primera vez - Limpieza profunda:**

```bash
# 1. Crear tabla de blacklist en BD
php artisan migrate

# 2. Ver qué necesita limpieza (sin eliminar)
php artisan cleanup:keywords-aliases --dry-run

# 3. Ejecutar la limpieza
php artisan cleanup:keywords-aliases

# 4. Eliminar keywords específicos que hayas identificado
php artisan cleanup:blacklist-wikidata --add=Q3329789
php artisan cleanup:blacklist-wikidata --clean

# 5. (Opcional) Re-importar con nuevos filtros mejorados
php artisan import:wikidata-keywords
```

### **Para futuros imports:**

```bash
# Solo importar (ahora con filtros automáticos)
php artisan import:wikidata-keywords

# Si aparecen nuevas entidades inútiles, usarlas a la blacklist
php artisan cleanup:blacklist-wikidata --add=QXXXXX
```

---

## 📊 Tipos de Datos Excluidos

| Categoría | Ejemplos | Patrón |
|-----------|----------|--------|
| **Barcos** | HMCS Onondaga, HMS Victory, USS Enterprise | HMCS, HMS, USS, (S##), (D##) |
| **Taxonomía** | Orden, Género, Especie | orden, familia, género, especie |
| **Eventos** | Batalla de..., Guerra de... | batalla, guerra, evento |
| **Personas** | Apellidos, héroes, figuras | persona, person, héroe, figura |
| **Obras** | Películas, libros, novelas | película, film, libro, novela |
| **Empresas** | Marcas, corporaciones | empresa, company, marca, brand |
| **Códigos** | Modelos, clases genéricas | clase, modelo, tipo |

---

## 🔧 Personalización

### Agregar más patrones de exclusión

**Para imports futuros:**
- Archivo: `app/Console/Commands/ImportWikidataKeywords.php`
- Arrays: `$excludeTypes`, `$excludeKeywords`
- Método: `isUnwantedEntity()` - agregar patrones regex

**Para limpieza:**
- Archivo: `app/Console/Commands/CleanupKeywordsAndAliases.php`
- Métodos: `countUnwantedKeywords()`, `deleteUnwantedKeywords()`

### Personalizar criterios SPARQL

Editar en `ImportWikidataKeywords.php` el método `buildLocationFilter()`:
```php
protected function buildLocationFilter(string $tipo): string {
    // Agregar criterios SPARQL específicos por tipo
    // P625 = coordenadas
    // P1566 = Geonames ID
    // P1104 = población
    // P131 = ubicación administrativa
}
```

---

## 💡 Próximos Pasos

1. ✅ Ejecutar migración: `php artisan migrate`
2. ✅ Limpiar datos existentes: `php artisan cleanup:keywords-aliases`
3. ✅ Agregar IDs específicos a blacklist si es necesario
4. ✅ Reintentar imports: `php artisan import:wikidata-keywords`
