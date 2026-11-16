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
} from "ckeditor5";

// On cible une CLASSE, pas un id : Symfony rend déjà l'id propre du champ
// (« decryptage_description »…), et un second id posé par le formulaire est
// supprimé par le parseur — « #editor » ne résolvait jamais, l'éditeur ne se
// montait sur aucun formulaire. La classe, elle, survit et se cumule.
document.querySelectorAll(".js-ckeditor").forEach(function (element) {
ClassicEditor.create(element, {
  licenseKey: "GPL",
  plugins: [
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
  ],
  toolbar: [
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
    "sourceEditing",
  ],
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
    // Un seul éditeur par page dans l'admin : window.editor reste la poignée
    // qu'utilise le bouton « Générer un brouillon » des décryptages.
    window.editor = editor;
  })
  .catch((error) => {
    console.error(error);
  });
});
