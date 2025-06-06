
/**
 * Initialisation de CKEditor (mode simple ou avancé)
 *
 *À inclure dans la vue :
 *
 * 1. Importmap :
 * <script type="importmap">
 * {
 *   "imports": {
 *     "ckeditor5": "<?= asset_url() ?>js/libraries/ckeditor/ckeditor5.js",
 *     "ckeditor5/": "<?= asset_url() ?>js/libraries/ckeditor/"
 *   }
 * }
 * </script>
 *
 * 2. Script d'initialisation :
 * <script type="module">
 *   import { initCkeditor } from "<?= asset_url() ?>js/dashboard/init-ckeditor.js";
 *   initCkeditor('#editor', 'simple'); // ou 'advanced'
 * </script>
 *
 *  Remarques :
 * - Le mode "simple" contient les options de base (gras, liste, lien, alignement, etc.).
 * - Le mode "advanced" ajoute les fonctionnalités enrichies :
 *   images, tableaux, embed vidéo, blockquotes, etc.
 *
 *  Remplace '#editor' par le sélecteur de ton champ.
 */

import {
  ClassicEditor,
  Essentials,
  Paragraph,
  Bold,
  Italic,
  Heading,
  Link,
  List,
  Indent,
  SourceEditing,
  Undo,
  Alignment,
  SimpleUploadAdapter,
 //Only advanced
  Image,
  ImageToolbar,
  ImageUpload,
  ImageCaption,
  ImageStyle,
  BlockQuote,
  Table,
  TableToolbar,
  MediaEmbed
} from "ckeditor5";

export function initCkeditor(selector, mode = "simple") {
  const advancedPlugins = [
    Image,
    ImageToolbar,
    ImageUpload,
    ImageCaption,
    ImageStyle,
    BlockQuote,
    Table,
    TableToolbar,
    MediaEmbed
  ];

  const basePlugins = [
    Essentials,
    Paragraph,
    Heading,
    Bold,
    Italic,
    Link,
    List,
    Indent,
    SourceEditing,
    Alignment,
    SimpleUploadAdapter,
  ];

  const plugins = mode === "advanced" ? [...basePlugins, ...advancedPlugins] : basePlugins;

  const toolbar = [
    "undo",
    "redo",
    "|",
    "heading",
    "|",
    "bold",
    "italic",
    "alignment",
    "|",
    "bulletedList",
    "numberedList",
    "outdent",
    "indent",
    "|",
    "link",
    "|",
    ...(mode === "advanced" ? ["uploadImage", "blockQuote", "insertTable", "mediaEmbed", "|"] : []),
    "sourceEditing",
  ];

  ClassicEditor.create(document.querySelector(selector), {
    licenseKey: "GPL",
    plugins,
    toolbar,
    link: {
      decorators: {
        isExternal: {
          mode: "automatic",
          callback: (url) => !url.startsWith("https://datan.fr"),
          attributes: {
            target: "_blank",
            rel: "noopener noreferrer",
          },
        },
      },
    },
    simpleUpload: {
      uploadUrl: "/upload/image",
      withCredentials: false,
    },
  })
    .then((editor) => {
      window.editor = editor;
    })
    .catch(console.error);
}


