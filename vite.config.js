import { resolve } from "path";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import i18nExtractKeys from "./i18nExtractKeys.vite.js";

export default defineConfig({
  plugins: [
    i18nExtractKeys({
      extraKeys: [
        "plugins.generic.scieloScreening.info.documentOrcidsOkay",
        "plugins.generic.scieloScreening.info.documentOrcidsUnableNoFile",
        "plugins.generic.scieloScreening.info.documentOrcidsUnableNoOrcids",
        "plugins.generic.scieloScreening.info.documentOrcidsUnableException",
      ],
    }),
    vue(),
  ],
  build: {
    target: "es2016",
    lib: {
      entry: resolve(__dirname, "resources/js/main.js"),
      name: "ScieloScreeningPlugin",
      fileName: "build",
      formats: ["iife"],
    },
    outDir: resolve(__dirname, "public/build"),
    rollupOptions: {
      external: ["vue"],
      output: {
        globals: {
          vue: "pkp.modules.vue",
        },
      },
    },
  },
});
