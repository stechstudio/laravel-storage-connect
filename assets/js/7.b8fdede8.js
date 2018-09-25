(window.webpackJsonp=window.webpackJsonp||[]).push([[7],{173:function(t,a,s){"use strict";s.r(a);var n=s(0),e=Object(n.a)({},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"content"},[t._m(0),t._v(" "),s("p",[t._v("Perhaps it doesn't make sense for your app to manage storage connections from an Eloquent model. Maybe you aren't setting up storage for multiple users or organizations, but rather one single storage connection for your whole app.")]),t._v(" "),s("p",[t._v("In this case you can bypass the entire Eloquent workflow and provide two callbacks for saving and loading a storage connection.")]),t._v(" "),t._m(1),t._v(" "),s("p",[t._v("In your AppServiceProvider boot method tell StorageConnect how to load and save:")]),t._v(" "),t._m(2),t._m(3),t._v(" "),s("p",[t._v("You will need to setup a route for the OAuth flow. You can optionally provide a final redirect location.")]),t._v(" "),t._m(4),t._m(5),t._v(" "),s("p",[t._v("If no redirect is provided, the final redirect will be used from your "),s("router-link",{attrs:{to:"./configuration.html#redirecting-after-oauth"}},[t._v("configuration setting")]),t._v(".")],1),t._v(" "),t._m(6),t._v(" "),t._m(7),t._v(" "),t._m(8)])},[function(){var t=this.$createElement,a=this._self._c||t;return a("h1",{attrs:{id:"custom-managed-storage"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#custom-managed-storage","aria-hidden":"true"}},[this._v("#")]),this._v(" Custom Managed Storage")])},function(){var t=this.$createElement,a=this._self._c||t;return a("h2",{attrs:{id:"provide-callbacks"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#provide-callbacks","aria-hidden":"true"}},[this._v("#")]),this._v(" Provide callbacks")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[t._v("StorageConnect"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("saveUsing")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token keyword"}},[t._v("function")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token variable"}},[t._v("$storage")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token variable"}},[t._v("$driver")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),s("span",{attrs:{class:"token comment"}},[t._v("// Store the connection wherever you want ")]),t._v("\n    Storage"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("put")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token variable"}},[t._v("$driver")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),t._v(" "),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'_connection.json'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token variable"}},[t._v("$storage")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),s("span",{attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n\nStorageConnect"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("loadUsing")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token keyword"}},[t._v("function")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token variable"}},[t._v("$driver")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),s("span",{attrs:{class:"token keyword"}},[t._v("return")]),t._v(" Storage"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("has")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token variable"}},[t._v("$driver")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),t._v(" "),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'_connection.json'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n        "),s("span",{attrs:{class:"token operator"}},[t._v("?")]),t._v(" Storage"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("get")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token variable"}},[t._v("$driver")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),t._v(" "),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'_connection.json'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n        "),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),t._v(" "),s("span",{attrs:{class:"token keyword"}},[t._v("null")]),t._v("'\n"),s("span",{attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])},function(){var t=this.$createElement,a=this._self._c||t;return a("h2",{attrs:{id:"authorize-cloud-storage"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#authorize-cloud-storage","aria-hidden":"true"}},[this._v("#")]),this._v(" Authorize cloud storage")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[t._v("Route"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("get")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'/my-authorize-endpoint'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{attrs:{class:"token keyword"}},[t._v("function")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),s("span",{attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),s("span",{attrs:{class:"token keyword"}},[t._v("return")]),t._v(" StorageConnect"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("driver")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'dropbox'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token function"}},[t._v("authorize")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token double-quoted-string string"}},[t._v('"/dashboard"')]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),s("span",{attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])},function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("This will take the your through the OAuth flow, create the cloud storage connection, save it using your custom callback, and finally redirect to "),a("code",[this._v("/dashboard")]),this._v(" when finished.")])},function(){var t=this.$createElement,a=this._self._c||t;return a("h2",{attrs:{id:"upload-files"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#upload-files","aria-hidden":"true"}},[this._v("#")]),this._v(" Upload files")])},function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("You can load your cloud storage connection by using the "),a("code",[this._v("driver")]),this._v(" method on the StorageConnect facade:")])},function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"language-php extra-class"},[s("pre",{pre:!0,attrs:{class:"language-php"}},[s("code",[t._v("StorageConnect"),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token punctuation"}},[t._v(":")]),s("span",{attrs:{class:"token function"}},[t._v("driver")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token single-quoted-string string"}},[t._v("'dropbox'")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token operator"}},[t._v("-")]),s("span",{attrs:{class:"token operator"}},[t._v(">")]),s("span",{attrs:{class:"token function"}},[t._v("upload")]),s("span",{attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])}],!1,null,null,null);e.options.__file="custom-managed-storage.md";a.default=e.exports}}]);