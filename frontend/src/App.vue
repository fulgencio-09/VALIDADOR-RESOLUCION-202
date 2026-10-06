<script setup>
import { ref } from 'vue'

const file = ref(null)
const status = ref('Listo para validar')

function selectFile(event) {
  file.value = event.target.files?.[0] ?? null
  status.value = file.value ? `Archivo seleccionado: ${file.value.name}` : 'Listo para validar'
}
</script>

<template>
  <main class="app-shell">
    <section class="hero">
      <span class="badge">RESOLUCIÓN 202 DE 2021</span>
      <h1>Validador de información</h1>
      <p>
        Plataforma para cargar, validar, analizar y corregir archivos mediante un
        motor de reglas versionado.
      </p>
    </section>

    <section class="card upload-card">
      <h2>Validar archivo</h2>
      <p class="muted">Seleccione el archivo que desea procesar.</p>

      <label class="dropzone">
        <input type="file" @change="selectFile" />
        <strong>{{ file ? file.name : 'Seleccionar archivo' }}</strong>
        <span>TXT / formatos soportados por el proceso</span>
      </label>

      <div class="status">{{ status }}</div>
      <button class="primary" :disabled="!file">Iniciar validación</button>
    </section>

    <section class="metrics">
      <article class="card"><strong>0</strong><span>Validaciones</span></article>
      <article class="card"><strong>0</strong><span>Errores</span></article>
      <article class="card"><strong>0</strong><span>Advertencias</span></article>
      <article class="card"><strong>0</strong><span>Correcciones</span></article>
    </section>
  </main>
</template>
