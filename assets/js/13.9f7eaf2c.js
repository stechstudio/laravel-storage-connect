(window.webpackJsonp=window.webpackJsonp||[]).push([[13],{167:function(t,a,s){"use strict";s.r(a);var n=s(0),e=Object(n.a)({},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"content"},[t._m(0),t._v(" "),s("p",[t._v("Once you have a cloud storage authorized and connected you can begin uploading files.")]),t._v(" "),t._m(1),t._m(2),t._v(" "),s("p",[t._v("By default a queue job is created to handle the upload. This avoids locking up your app during a large upload and ensures any upload failures are retried.")]),t._v(" "),s("p",[t._v("If you really don't want the upload to be queued you can pass false in as a third argument.")]),t._v(" "),t._m(3),t._m(4),t._v(" "),t._m(5),t._v(" "),t._m(6),t._m(7),t._v(" "),s("p",[t._v("You can also upload from a URL.")]),t._v(" "),t._m(8),t._m(9),t._v(" "),s("p",[t._v("If you are using Google, this package will download the file from the URL first, and then do a normal upload.")]),t._v(" "),t._m(10),t._v(" "),s("p",[t._v("In all the above examples we are explicitly providing the source and destination paths as strings. There is also an option to just point to an Eloquent model for the upload.")]),t._v(" "),t._m(11),t._v(" "),t._m(12),s("p",[t._v("Now you can upload an instance of this model directly:")]),t._v(" "),t._m(13),s("p",[t._v("One big advantage of this approach is that all the "),s("router-link",{attrs:{to:"./events.html"}},[t._v("upload related events")]),t._v(" will now include this target model, for your reference.")],1),t._v(" "),t._m(14)])},[function(){var t=this.$createElement,a=this._self._c||t;return a("h1",{attrs:{id:"upload-files"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#upload-files","aria-hidden":"true"}},[this._v("#")]),this._v(" Upload Files")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[s("span",{attrs:{class:"token variable"}},[t._v("$model")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token property"}},[t._v("dropbox")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token function"}},[t._v("upload")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'/path/to/source.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'My File.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])},function(){var t=this.$createElement,a=this._self._c||t;return a("h2",{attrs:{id:"queued-uploads"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#queued-uploads","aria-hidden":"true"}},[this._v("#")]),this._v(" Queued uploads")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[s("span",{attrs:{class:"token variable"}},[t._v("$model")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token property"}},[t._v("dropbox")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token function"}},[t._v("upload")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'/path/to/source.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'My File.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token boolean"}},[t._v("false")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])},function(){var t=this.$createElement,a=this._self._c||t;return a("h2",{attrs:{id:"uploading-from-s3"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#uploading-from-s3","aria-hidden":"true"}},[this._v("#")]),this._v(" Uploading from S3")])},function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("You can upload files from an S3 bucket by using the "),a("code",[this._v("s3://")]),this._v(" protocol. This assumes, of course, you have your AWS credentials setup in the .env file and read access to the bucket where the files are stored.")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[s("span",{attrs:{class:"token variable"}},[t._v("$model")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token property"}},[t._v("dropbox")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token function"}},[t._v("upload")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'s3://bucket-name/source.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'My File.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])},function(){var t=this.$createElement,a=this._self._c||t;return a("h2",{attrs:{id:"uploading-from-url"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#uploading-from-url","aria-hidden":"true"}},[this._v("#")]),this._v(" Uploading from URL")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[s("span",{attrs:{class:"token variable"}},[t._v("$model")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token property"}},[t._v("dropbox")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token function"}},[t._v("upload")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'https://www.somewebsite.com/source.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'My File.pdf'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])},function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("If you are using Dropbox, this will use the "),a("code",[this._v("save_url")]),this._v(" method. Dropbox will pull the file directly from the URL.")])},function(){var t=this.$createElement,a=this._self._c||t;return a("h2",{attrs:{id:"uploading-an-eloquent-model"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#uploading-an-eloquent-model","aria-hidden":"true"}},[this._v("#")]),this._v(" Uploading an Eloquent model")])},function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("Perhaps you have a "),a("code",[this._v("files")]),this._v(" table in your database. Edit the "),a("code",[this._v("Files")]),this._v(" model, implement the "),a("code",[this._v("UploadTarget")]),this._v(" contract, and add two accessors like this:")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[s("span",{attrs:{class:"token keyword"}},[t._v("namespace")]),t._v(" "),s("span",{attrs:{class:"token package"}},[t._v("App"),s("span",{attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Models")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n\n"),s("span",{attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),s("span",{attrs:{class:"token package"}},[t._v("Illuminate"),s("span",{attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Database"),s("span",{attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Eloquent"),s("span",{attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Model")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),s("span",{attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),s("span",{attrs:{class:"token package"}},[t._v("STS"),s("span",{attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("StorageConnect"),s("span",{attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Contracts"),s("span",{attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("UploadTarget")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n\n"),s("span",{attrs:{class:"token keyword"}},[t._v("class")]),t._v(" "),s("span",{attrs:{class:"token class-name"}},[t._v("File")]),t._v(" "),s("span",{attrs:{class:"token keyword"}},[t._v("extends")]),t._v(" "),s("span",{attrs:{class:"token class-name"}},[t._v("Model")]),t._v(" "),s("span",{attrs:{class:"token keyword"}},[t._v("implements")]),t._v(" "),s("span",{attrs:{class:"token class-name"}},[t._v("UploadTarget")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    \n    "),s("span",{attrs:{class:"token keyword"}},[t._v("public")]),t._v(" "),s("span",{attrs:{class:"token keyword"}},[t._v("function")]),t._v(" "),s("span",{attrs:{class:"token function"}},[t._v("getUploadSourcePathAttribute")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n    "),s("span",{attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),s("span",{attrs:{class:"token comment"}},[t._v("// Return the source path for this file    ")]),t._v("\n    "),s("span",{attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n    \n    "),s("span",{attrs:{class:"token keyword"}},[t._v("public")]),t._v(" "),s("span",{attrs:{class:"token keyword"}},[t._v("function")]),t._v(" "),s("span",{attrs:{class:"token function"}},[t._v("getUploadDestinationPathAttribute")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n    "),s("span",{attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),s("span",{attrs:{class:"token comment"}},[t._v("// Return the destination path for the upload")]),t._v("\n    "),s("span",{attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n    \n    "),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),t._v("\n")])])])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[s("span",{attrs:{class:"token variable"}},[t._v("$model")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token property"}},[t._v("dropbox")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token function"}},[t._v("upload")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token variable"}},[t._v("$file")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[t._v("Event"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("listen")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),t._v("UploadSucceeded"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token keyword"}},[t._v("class")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token keyword"}},[t._v("function")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token variable"}},[t._v("$event")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n       "),s("span",{attrs:{class:"token comment"}},[t._v("// $event->target is a reference to the $file model you asked to upload")]),t._v("\n"),s("span",{attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])}],!1,null,null,null);e.options.__file="uploading-files.md";a.default=e.exports}}]);