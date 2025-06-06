// import {
//   ClassicEditor,
//   Essentials,
//   Paragraph,
//   Bold,
//   Italic,
//   Heading,
//   Link,
//   List,
//   Indent,
//   SourceEditing,
//   Undo,
//   Alignment,
//   SimpleUploadAdapter,
// } from "ckeditor5";

// ClassicEditor.create(document.querySelector("#editor"), {
//   licenseKey: "GPL",
//   plugins: [
//     Essentials,
//     Paragraph,
//     Heading,
//     Bold,
//     Italic,
//     Link,
//     List,
//     Indent,
//     SourceEditing,
//     Alignment,
//     SimpleUploadAdapter,
//   ],
//   toolbar: [
//     "undo",
//     "redo",
//     "|",
//     "heading",
//     "|",
//     "bold",
//     "italic",
//     "alignment",
//     "|",
//     "bulletedList",
//     "numberedList",
//     "outdent",
//     "indent",
//     "|",
//     "link",
//     "|",
//     "sourceEditing",
//   ],
//   link: {
//     decorators: {
//       isExternal: {
//         mode: "automatic",
//         callback: (url) => !url.startsWith("https://datan.fr"),
//         attributes: {
//           target: "_blank",
//           rel: "noopener noreferrer",
//         },
//       },
//     },
//   },
//   simpleUpload: {
//     uploadUrl: "/upload/image",
//     withCredentials: false,
//   },
// })
//   .then((editor) => {
//     window.editor = editor;
//   })
//   .catch((error) => {
//     console.error(error);
//   });

console.log("test")
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


