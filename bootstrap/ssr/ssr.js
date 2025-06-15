var __defProp = Object.defineProperty;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);
import { router, setupProgress } from "@inertiajs/core";
import { clsx as clsx$1 } from "clsx";
import escape from "html-escape";
import createServer from "@inertiajs/core/server";
const HYDRATION_START = "[";
const HYDRATION_END = "]";
const ELEMENT_IS_NAMESPACED = 1;
const ELEMENT_PRESERVE_ATTRIBUTE_CASE = 1 << 1;
const ATTR_REGEX = /[&"<]/g;
const CONTENT_REGEX = /[&<]/g;
function escape_html(value, is_attr) {
  const str = String(value ?? "");
  const pattern = is_attr ? ATTR_REGEX : CONTENT_REGEX;
  pattern.lastIndex = 0;
  let escaped = "";
  let last = 0;
  while (pattern.test(str)) {
    const i = pattern.lastIndex - 1;
    const ch = str[i];
    escaped += str.substring(last, i) + (ch === "&" ? "&amp;" : ch === '"' ? "&quot;" : "&lt;");
    last = i + 1;
  }
  return escaped + str.substring(last);
}
const replacements = {
  translate: /* @__PURE__ */ new Map([
    [true, "yes"],
    [false, "no"]
  ])
};
function attr(name, value, is_boolean = false) {
  if (value == null || !value && is_boolean) return "";
  const normalized = name in replacements && replacements[name].get(value) || value;
  const assignment = is_boolean ? "" : `="${escape_html(normalized, true)}"`;
  return ` ${name}${assignment}`;
}
function clsx(value) {
  if (typeof value === "object") {
    return clsx$1(value);
  } else {
    return value ?? "";
  }
}
const whitespace = [..." 	\n\r\f \v\uFEFF"];
function to_class(value, hash, directives) {
  var classname = value == null ? "" : "" + value;
  if (hash) {
    classname = classname ? classname + " " + hash : hash;
  }
  if (directives) {
    for (var key in directives) {
      if (directives[key]) {
        classname = classname ? classname + " " + key : key;
      } else if (classname.length) {
        var len = key.length;
        var a = 0;
        while ((a = classname.indexOf(key, a)) >= 0) {
          var b = a + len;
          if ((a === 0 || whitespace.includes(classname[a - 1])) && (b === classname.length || whitespace.includes(classname[b]))) {
            classname = (a === 0 ? "" : classname.substring(0, a)) + classname.substring(b + 1);
          } else {
            a = b;
          }
        }
      }
    }
  }
  return classname === "" ? null : classname;
}
const noop = () => {
};
function fallback(value, fallback2, lazy = false) {
  return value === void 0 ? lazy ? (
    /** @type {() => V} */
    fallback2()
  ) : (
    /** @type {V} */
    fallback2
  ) : value;
}
function safe_not_equal(a, b) {
  return a != a ? b == b : a !== b || a !== null && typeof a === "object" || typeof a === "function";
}
let untracking = false;
function untrack(fn) {
  var previous_untracking = untracking;
  try {
    untracking = true;
    return fn();
  } finally {
    untracking = previous_untracking;
  }
}
const DOM_BOOLEAN_ATTRIBUTES = [
  "allowfullscreen",
  "async",
  "autofocus",
  "autoplay",
  "checked",
  "controls",
  "default",
  "disabled",
  "formnovalidate",
  "hidden",
  "indeterminate",
  "inert",
  "ismap",
  "loop",
  "multiple",
  "muted",
  "nomodule",
  "novalidate",
  "open",
  "playsinline",
  "readonly",
  "required",
  "reversed",
  "seamless",
  "selected",
  "webkitdirectory",
  "defer",
  "disablepictureinpicture",
  "disableremoteplayback"
];
function is_boolean_attribute(name) {
  return DOM_BOOLEAN_ATTRIBUTES.includes(name);
}
const subscriber_queue = [];
function writable(value, start = noop) {
  let stop = null;
  const subscribers = /* @__PURE__ */ new Set();
  function set2(new_value) {
    if (safe_not_equal(value, new_value)) {
      value = new_value;
      if (stop) {
        const run_queue = !subscriber_queue.length;
        for (const subscriber of subscribers) {
          subscriber[1]();
          subscriber_queue.push(subscriber, value);
        }
        if (run_queue) {
          for (let i = 0; i < subscriber_queue.length; i += 2) {
            subscriber_queue[i][0](subscriber_queue[i + 1]);
          }
          subscriber_queue.length = 0;
        }
      }
    }
  }
  function update(fn) {
    set2(fn(
      /** @type {T} */
      value
    ));
  }
  function subscribe2(run, invalidate = noop) {
    const subscriber = [run, invalidate];
    subscribers.add(subscriber);
    if (subscribers.size === 1) {
      stop = start(set2, update) || noop;
    }
    run(
      /** @type {T} */
      value
    );
    return () => {
      subscribers.delete(subscriber);
      if (subscribers.size === 0 && stop) {
        stop();
        stop = null;
      }
    };
  }
  return { set: set2, update, subscribe: subscribe2 };
}
function subscribe_to_store(store, run, invalidate) {
  if (store == null) {
    run(void 0);
    return noop;
  }
  const unsub = untrack(
    () => store.subscribe(
      run,
      // @ts-expect-error
      invalidate
    )
  );
  return unsub.unsubscribe ? () => unsub.unsubscribe() : unsub;
}
var current_component = null;
function push(fn) {
  current_component = { p: current_component, c: null, d: null };
}
function pop() {
  var component = (
    /** @type {Component} */
    current_component
  );
  var ondestroy = component.d;
  if (ondestroy) {
    on_destroy.push(...ondestroy);
  }
  current_component = component.p;
}
const BLOCK_OPEN = `<!--${HYDRATION_START}-->`;
const BLOCK_CLOSE = `<!--${HYDRATION_END}-->`;
class HeadPayload {
  constructor(css = /* @__PURE__ */ new Set(), out = "", title = "", uid = () => "") {
    /** @type {Set<{ hash: string; code: string }>} */
    __publicField(this, "css", /* @__PURE__ */ new Set());
    __publicField(this, "out", "");
    __publicField(this, "uid", () => "");
    __publicField(this, "title", "");
    this.css = css;
    this.out = out;
    this.title = title;
    this.uid = uid;
  }
}
class Payload {
  constructor(id_prefix = "") {
    /** @type {Set<{ hash: string; code: string }>} */
    __publicField(this, "css", /* @__PURE__ */ new Set());
    __publicField(this, "out", "");
    __publicField(this, "uid", () => "");
    __publicField(this, "select_value");
    __publicField(this, "head", new HeadPayload());
    this.uid = props_id_generator(id_prefix);
    this.head.uid = this.uid;
  }
}
function props_id_generator(prefix) {
  let uid = 1;
  return () => `${prefix}s${uid++}`;
}
const INVALID_ATTR_NAME_CHAR_REGEX = /[\s'">/=\u{FDD0}-\u{FDEF}\u{FFFE}\u{FFFF}\u{1FFFE}\u{1FFFF}\u{2FFFE}\u{2FFFF}\u{3FFFE}\u{3FFFF}\u{4FFFE}\u{4FFFF}\u{5FFFE}\u{5FFFF}\u{6FFFE}\u{6FFFF}\u{7FFFE}\u{7FFFF}\u{8FFFE}\u{8FFFF}\u{9FFFE}\u{9FFFF}\u{AFFFE}\u{AFFFF}\u{BFFFE}\u{BFFFF}\u{CFFFE}\u{CFFFF}\u{DFFFE}\u{DFFFF}\u{EFFFE}\u{EFFFF}\u{FFFFE}\u{FFFFF}\u{10FFFE}\u{10FFFF}]/u;
let on_destroy = [];
function render(component, options = {}) {
  const payload = new Payload(options.idPrefix ? options.idPrefix + "-" : "");
  const prev_on_destroy = on_destroy;
  on_destroy = [];
  payload.out += BLOCK_OPEN;
  if (options.context) {
    push();
    current_component.c = options.context;
  }
  component(payload, options.props ?? {}, {}, {});
  if (options.context) {
    pop();
  }
  payload.out += BLOCK_CLOSE;
  for (const cleanup of on_destroy) cleanup();
  on_destroy = prev_on_destroy;
  let head2 = payload.head.out + payload.head.title;
  for (const { hash, code } of payload.css) {
    head2 += `<style id="${hash}">${code}</style>`;
  }
  return {
    head: head2,
    html: payload.out,
    body: payload.out
  };
}
function head(payload, fn) {
  const head_payload = payload.head;
  head_payload.out += BLOCK_OPEN;
  fn(head_payload);
  head_payload.out += BLOCK_CLOSE;
}
function spread_attributes(attrs, css_hash, classes, styles, flags = 0) {
  if (attrs.class) {
    attrs.class = clsx(attrs.class);
  }
  if (classes) {
    attrs.class = to_class(attrs.class, css_hash, classes);
  }
  let attr_str = "";
  let name;
  const is_html = (flags & ELEMENT_IS_NAMESPACED) === 0;
  const lowercase = (flags & ELEMENT_PRESERVE_ATTRIBUTE_CASE) === 0;
  for (name in attrs) {
    if (typeof attrs[name] === "function") continue;
    if (name[0] === "$" && name[1] === "$") continue;
    if (INVALID_ATTR_NAME_CHAR_REGEX.test(name)) continue;
    var value = attrs[name];
    if (lowercase) {
      name = name.toLowerCase();
    }
    attr_str += attr(name, value, is_html && is_boolean_attribute(name));
  }
  return attr_str;
}
function spread_props(props) {
  const merged_props = {};
  let key;
  for (let i = 0; i < props.length; i++) {
    const obj = props[i];
    for (key in obj) {
      const desc = Object.getOwnPropertyDescriptor(obj, key);
      if (desc) {
        Object.defineProperty(merged_props, key, desc);
      } else {
        merged_props[key] = obj[key];
      }
    }
  }
  return merged_props;
}
function stringify(value) {
  return typeof value === "string" ? value : value == null ? "" : value + "";
}
function attr_class(value, hash, directives) {
  var result = to_class(value, hash, directives);
  return result ? ` class="${escape_html(result, true)}"` : "";
}
function store_get(store_values, store_name, store) {
  var _a;
  if (store_name in store_values && store_values[store_name][0] === store) {
    return store_values[store_name][2];
  }
  (_a = store_values[store_name]) == null ? void 0 : _a[1]();
  store_values[store_name] = [store, null, void 0];
  const unsub = subscribe_to_store(
    store,
    /** @param {any} v */
    (v) => store_values[store_name][2] = v
  );
  store_values[store_name][1] = unsub;
  return store_values[store_name][2];
}
function unsubscribe_stores(store_values) {
  for (const store_name in store_values) {
    store_values[store_name][1]();
  }
}
function slot(payload, $$props, name, slot_props, fallback_fn) {
  var _a;
  var slot_fn = (_a = $$props.$$slots) == null ? void 0 : _a[name];
  if (slot_fn === true) {
    slot_fn = $$props[name === "default" ? "children" : name];
  }
  if (slot_fn !== void 0) {
    slot_fn(payload, slot_props);
  } else {
    fallback_fn == null ? void 0 : fallback_fn();
  }
}
function rest_props(props, rest) {
  const rest_props2 = {};
  let key;
  for (key in props) {
    if (!rest.includes(key)) {
      rest_props2[key] = props[key];
    }
  }
  return rest_props2;
}
function sanitize_props(props) {
  const { children, $$slots, ...sanitized } = props;
  return sanitized;
}
function bind_props(props_parent, props_now) {
  var _a;
  for (const key in props_now) {
    const initial_value = props_parent[key];
    const value = props_now[key];
    if (initial_value === void 0 && value !== void 0 && ((_a = Object.getOwnPropertyDescriptor(props_parent, key)) == null ? void 0 : _a.set)) {
      props_parent[key] = value;
    }
  }
}
function ensure_array_like(array_like_or_iterator) {
  if (array_like_or_iterator) {
    return array_like_or_iterator.length !== void 0 ? array_like_or_iterator : Array.from(array_like_or_iterator);
  }
  return [];
}
function onDestroy(fn) {
  var context = (
    /** @type {Component} */
    current_component
  );
  (context.d ?? (context.d = [])).push(fn);
}
const h = (component, propsOrChildren, childrenOrKey, key = null) => {
  const hasProps = typeof propsOrChildren === "object" && propsOrChildren !== null && !Array.isArray(propsOrChildren);
  return {
    component,
    key: hasProps ? key : typeof childrenOrKey === "number" ? childrenOrKey : null,
    props: hasProps ? propsOrChildren : {},
    children: hasProps ? Array.isArray(childrenOrKey) ? childrenOrKey : childrenOrKey !== null ? [childrenOrKey] : [] : Array.isArray(propsOrChildren) ? propsOrChildren : propsOrChildren !== null ? [propsOrChildren] : []
  };
};
function Render($$payload, $$props) {
  push();
  let component = $$props["component"];
  let props = fallback($$props["props"], () => ({}), true);
  let children = fallback($$props["children"], () => [], true);
  let key = fallback($$props["key"], null);
  if (component) {
    $$payload.out += "<!--[-->";
    $$payload.out += `<!---->`;
    {
      if (children.length > 0) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<!---->`;
        component == null ? void 0 : component($$payload, spread_props([
          props,
          {
            children: ($$payload2) => {
              const each_array = ensure_array_like(children);
              $$payload2.out += `<!--[-->`;
              for (let $$index = 0, $$length = each_array.length; $$index < $$length; $$index++) {
                let child = each_array[$$index];
                Render($$payload2, spread_props([child]));
                $$payload2.out += `<!---->`;
              }
              $$payload2.out += `<!--]-->`;
            },
            $$slots: { default: true }
          }
        ]));
        $$payload.out += `<!---->`;
      } else {
        $$payload.out += "<!--[!-->";
        $$payload.out += `<!---->`;
        component == null ? void 0 : component($$payload, spread_props([props]));
        $$payload.out += `<!---->`;
      }
      $$payload.out += `<!--]-->`;
    }
    $$payload.out += `<!---->`;
  } else {
    $$payload.out += "<!--[!-->";
  }
  $$payload.out += `<!--]-->`;
  bind_props($$props, { component, props, children, key });
  pop();
}
const { set, subscribe } = writable();
const setPage = set;
const page = { subscribe };
function App$1($$payload, $$props) {
  push();
  let initialComponent = $$props["initialComponent"];
  let initialPage = $$props["initialPage"];
  let resolveComponent = $$props["resolveComponent"];
  let component = initialComponent;
  let key = null;
  let page2 = initialPage;
  let renderProps = resolveRenderProps(component, page2, key);
  setPage(page2);
  const isServer = typeof window === "undefined";
  if (!isServer) {
    router.init({
      initialPage,
      resolveComponent,
      swapComponent: async (args) => {
        component = args.component;
        page2 = args.page;
        key = args.preserveState ? key : Date.now();
        renderProps = resolveRenderProps(component, page2, key);
        setPage(page2);
      }
    });
  }
  function resolveRenderProps(component2, page22, key2 = null) {
    const child = h(component2.default, page22.props, [], key2);
    const layout = component2.layout;
    return layout ? resolveLayout(layout, child, page22.props, key2) : child;
  }
  function resolveLayout(layout, child, pageProps, key2) {
    if (isLayoutFunction(layout)) {
      return layout(h, child);
    }
    if (Array.isArray(layout)) {
      return layout.slice().reverse().reduce((currentRender, layoutComponent) => h(layoutComponent, pageProps, [currentRender], key2), child);
    }
    return h(layout, pageProps, child ? [child] : [], key2);
  }
  function isLayoutFunction(layout) {
    return typeof layout === "function" && layout.length === 2 && typeof layout.prototype === "undefined";
  }
  Render($$payload, spread_props([renderProps]));
  bind_props($$props, {
    initialComponent,
    initialPage,
    resolveComponent
  });
  pop();
}
async function createInertiaApp({ id = "app", resolve, setup, progress = {}, page: page2 }) {
  const isServer = typeof window === "undefined";
  const el = isServer ? null : document.getElementById(id);
  const initialPage = page2 || JSON.parse((el == null ? void 0 : el.dataset.page) || "{}");
  const resolveComponent = (name) => Promise.resolve(resolve(name));
  const [initialComponent] = await Promise.all([
    resolveComponent(initialPage.component),
    router.decryptHistory().catch(() => {
    })
  ]);
  const props = { initialPage, initialComponent, resolveComponent };
  const svelteApp = setup({
    el,
    App: App$1,
    props
  });
  if (isServer) {
    const { html, head: head2, css } = svelteApp;
    return {
      body: `<div data-server-rendered="true" id="${id}" data-page="${escape(JSON.stringify(initialPage))}">${html}</div>`,
      head: [head2, css ? `<style data-vite-css>${css.code}</style>` : ""]
    };
  }
  if (progress) {
    setupProgress(progress);
  }
}
function NavDropdown_1($$payload, $$props) {
  let { items, id } = $$props;
  const each_array = ensure_array_like(Object.entries(items));
  $$payload.out += `<ul class="dropdown-menu"${attr("aria-labelledby", `dropdown${stringify({ id })}`)}><!--[-->`;
  for (let $$index = 0, $$length = each_array.length; $$index < $$length; $$index++) {
    let [id2, item] = each_array[$$index];
    if (item.children) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="dropdown dropend"><a class="dropdown-item dropdown-toggle"${attr("href", item.url ?? "#")} role="button"${attr("id", `dropdown${stringify(id2)}`)} data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">${escape_html(item.name)}</a> `;
      NavDropdown($$payload, { items: item.children, id: id2 });
      $$payload.out += `<!----></li>`;
    } else {
      $$payload.out += "<!--[!-->";
      $$payload.out += `<li><a class="dropdown-item"${attr("href", item.url ?? "#")}>${escape_html(item.name)}</a></li>`;
    }
    $$payload.out += `<!--]-->`;
  }
  $$payload.out += `<!--]--></ul>`;
}
function toClassName(value) {
  let result = "";
  if (typeof value === "string" || typeof value === "number") {
    result += value;
  } else if (typeof value === "object") {
    if (Array.isArray(value)) {
      result = value.map(toClassName).filter(Boolean).join(" ");
    } else {
      for (let key in value) {
        if (value[key]) {
          result && (result += " ");
          result += key;
        }
      }
    }
  }
  return result;
}
const classnames = (...args) => args.map(toClassName).filter(Boolean).join(" ");
function uuid() {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
    const r = Math.random() * 16 | 0;
    const v = c === "x" ? r : r & 3 | 8;
    return v.toString(16);
  });
}
function Button($$payload, $$props) {
  const $$sanitized_props = sanitize_props($$props);
  const $$restProps = rest_props($$sanitized_props, [
    "class",
    "active",
    "block",
    "content",
    "close",
    "color",
    "disabled",
    "href",
    "inner",
    "outline",
    "size",
    "value"
  ]);
  push();
  let ariaLabel, classes, defaultAriaLabel;
  let className = fallback($$props["class"], "");
  let active = fallback($$props["active"], false);
  let block = fallback($$props["block"], false);
  let content = fallback($$props["content"], "");
  let close = fallback($$props["close"], false);
  let color = fallback($$props["color"], "secondary");
  let disabled = fallback($$props["disabled"], false);
  let href = fallback($$props["href"], "");
  let inner = fallback($$props["inner"], void 0);
  let outline = fallback($$props["outline"], false);
  let size = fallback($$props["size"], "");
  let value = fallback($$props["value"], "");
  ariaLabel = $$sanitized_props["aria-label"];
  classes = classnames(className, close ? "btn-close" : "btn", close || `btn${outline ? "-outline" : ""}-${color}`, size ? `btn-${size}` : false, block ? "d-block w-100" : false, { active });
  defaultAriaLabel = close ? "Close" : null;
  if (href) {
    $$payload.out += "<!--[-->";
    $$payload.out += `<a${spread_attributes(
      {
        ...$$restProps,
        class: clsx(classes),
        href,
        "aria-label": ariaLabel || defaultAriaLabel
      },
      null,
      { disabled }
    )}>`;
    if (content) {
      $$payload.out += "<!--[-->";
      $$payload.out += `${escape_html(content)}`;
    } else {
      $$payload.out += "<!--[!-->";
      $$payload.out += `<!---->`;
      slot($$payload, $$props, "default", {}, null);
      $$payload.out += `<!---->`;
    }
    $$payload.out += `<!--]--></a>`;
  } else {
    $$payload.out += "<!--[!-->";
    $$payload.out += `<button${spread_attributes(
      {
        ...$$restProps,
        class: clsx(classes),
        disabled,
        value,
        "aria-label": ariaLabel || defaultAriaLabel
      },
      null
    )}><!---->`;
    slot($$payload, $$props, "default", {}, () => {
      if (content) {
        $$payload.out += "<!--[-->";
        $$payload.out += `${escape_html(content)}`;
      } else {
        $$payload.out += "<!--[!-->";
        $$payload.out += `<!---->`;
        slot($$payload, $$props, "default", {}, null);
        $$payload.out += `<!---->`;
      }
      $$payload.out += `<!--]-->`;
    });
    $$payload.out += `<!----></button>`;
  }
  $$payload.out += `<!--]-->`;
  bind_props($$props, {
    class: className,
    active,
    block,
    content,
    close,
    color,
    disabled,
    href,
    inner,
    outline,
    size,
    value
  });
  pop();
}
function InlineContainer($$payload, $$props) {
  $$payload.out += `<div><!---->`;
  slot($$payload, $$props, "default", {}, null);
  $$payload.out += `<!----></div>`;
}
function ModalBackdrop($$payload, $$props) {
  const $$sanitized_props = sanitize_props($$props);
  const $$restProps = rest_props($$sanitized_props, ["class", "isOpen", "fade"]);
  push();
  let classes;
  let className = fallback($$props["class"], "");
  let isOpen = fallback($$props["isOpen"], false);
  let fade = fallback($$props["fade"], true);
  let loaded = false;
  classes = classnames(className, "modal-backdrop");
  if (isOpen && loaded) {
    $$payload.out += "<!--[-->";
    $$payload.out += `<div${spread_attributes(
      {
        role: "presentation",
        ...$$restProps,
        class: clsx(classes)
      },
      null,
      { fade }
    )}></div>`;
  } else {
    $$payload.out += "<!--[!-->";
  }
  $$payload.out += `<!--]-->`;
  bind_props($$props, { class: className, isOpen, fade });
  pop();
}
function ModalBody($$payload, $$props) {
  const $$sanitized_props = sanitize_props($$props);
  const $$restProps = rest_props($$sanitized_props, ["class"]);
  push();
  let classes;
  let className = fallback($$props["class"], "");
  classes = classnames(className, "modal-body");
  $$payload.out += `<div${spread_attributes({ ...$$restProps, class: clsx(classes) }, null)}><!---->`;
  slot($$payload, $$props, "default", {}, null);
  $$payload.out += `<!----></div>`;
  bind_props($$props, { class: className });
  pop();
}
function ModalHeader($$payload, $$props) {
  const $$sanitized_props = sanitize_props($$props);
  const $$restProps = rest_props($$sanitized_props, [
    "class",
    "toggle",
    "closeAriaLabel",
    "id",
    "content"
  ]);
  push();
  let classes;
  let className = fallback($$props["class"], "");
  let toggle2 = fallback($$props["toggle"], void 0);
  let closeAriaLabel = fallback($$props["closeAriaLabel"], "Close");
  let id = fallback($$props["id"], void 0);
  let content = fallback($$props["content"], void 0);
  classes = classnames(className, "modal-header");
  $$payload.out += `<div${spread_attributes({ ...$$restProps, class: clsx(classes) }, null)}><h5 class="modal-title"${attr("id", id)}>`;
  if (content) {
    $$payload.out += "<!--[-->";
    $$payload.out += `${escape_html(content)}`;
  } else {
    $$payload.out += "<!--[!-->";
    $$payload.out += `<!---->`;
    slot($$payload, $$props, "default", {}, null);
    $$payload.out += `<!---->`;
  }
  $$payload.out += `<!--]--></h5> <!---->`;
  slot($$payload, $$props, "close", {}, () => {
    if (typeof toggle2 === "function") {
      $$payload.out += "<!--[-->";
      $$payload.out += `<button type="button" class="btn-close"${attr("aria-label", closeAriaLabel)}></button>`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]-->`;
  });
  $$payload.out += `<!----></div>`;
  bind_props($$props, {
    class: className,
    toggle: toggle2,
    closeAriaLabel,
    id,
    content
  });
  pop();
}
function Portal($$payload, $$props) {
  const $$sanitized_props = sanitize_props($$props);
  const $$restProps = rest_props($$sanitized_props, []);
  push();
  onDestroy(() => {
  });
  $$payload.out += `<div${spread_attributes({ ...$$restProps }, null)}><!---->`;
  slot($$payload, $$props, "default", {}, null);
  $$payload.out += `<!----></div>`;
  pop();
}
function Modal($$payload, $$props) {
  const $$sanitized_props = sanitize_props($$props);
  rest_props($$sanitized_props, [
    "class",
    "static",
    "autoFocus",
    "body",
    "centered",
    "container",
    "fullscreen",
    "header",
    "isOpen",
    "keyboard",
    "backdrop",
    "contentClassName",
    "fade",
    "labelledBy",
    "modalClassName",
    "modalStyle",
    "returnFocusAfterClose",
    "scrollable",
    "size",
    "theme",
    "toggle",
    "unmountOnClose",
    "wrapClassName"
  ]);
  push();
  let outer;
  let className = fallback($$props["class"], "");
  let staticModal = fallback($$props["static"], false);
  let autoFocus = fallback($$props["autoFocus"], true);
  let body = fallback($$props["body"], false);
  let centered = fallback($$props["centered"], false);
  let container = fallback($$props["container"], void 0);
  let fullscreen = fallback($$props["fullscreen"], false);
  let header = fallback($$props["header"], void 0);
  let isOpen = fallback($$props["isOpen"], false);
  let keyboard = fallback($$props["keyboard"], true);
  let backdrop = fallback($$props["backdrop"], true);
  let contentClassName = fallback($$props["contentClassName"], "");
  let fade = fallback($$props["fade"], true);
  let labelledBy = fallback($$props["labelledBy"], () => header ? `modal-${uuid()}` : void 0, true);
  let modalClassName = fallback($$props["modalClassName"], "");
  let modalStyle = fallback($$props["modalStyle"], null);
  let returnFocusAfterClose = fallback($$props["returnFocusAfterClose"], true);
  let scrollable = fallback($$props["scrollable"], false);
  let size = fallback($$props["size"], "");
  let theme = fallback($$props["theme"], null);
  let toggle2 = fallback($$props["toggle"], void 0);
  let unmountOnClose = fallback($$props["unmountOnClose"], true);
  let wrapClassName = fallback($$props["wrapClassName"], "");
  onDestroy(() => {
  });
  const dialogBaseClass = "modal-dialog";
  classnames(dialogBaseClass, className, {
    [`modal-${size}`]: size,
    "modal-fullscreen": fullscreen === true,
    [`modal-fullscreen-${fullscreen}-down`]: fullscreen && typeof fullscreen === "string",
    [`${dialogBaseClass}-centered`]: centered,
    [`${dialogBaseClass}-scrollable`]: scrollable
  });
  outer = container === "inline" || staticModal ? InlineContainer : Portal;
  {
    $$payload.out += "<!--[!-->";
  }
  $$payload.out += `<!--]--> `;
  if (backdrop && !staticModal) {
    $$payload.out += "<!--[-->";
    $$payload.out += `<!---->`;
    outer == null ? void 0 : outer($$payload, {
      children: ($$payload2) => {
        ModalBackdrop($$payload2, { fade, isOpen });
      },
      $$slots: { default: true }
    });
    $$payload.out += `<!---->`;
  } else {
    $$payload.out += "<!--[!-->";
  }
  $$payload.out += `<!--]-->`;
  bind_props($$props, {
    class: className,
    static: staticModal,
    autoFocus,
    body,
    centered,
    container,
    fullscreen,
    header,
    isOpen,
    keyboard,
    backdrop,
    contentClassName,
    fade,
    labelledBy,
    modalClassName,
    modalStyle,
    returnFocusAfterClose,
    scrollable,
    size,
    theme,
    toggle: toggle2,
    unmountOnClose,
    wrapClassName
  });
  pop();
}
function ModalFooter($$payload, $$props) {
  const $$sanitized_props = sanitize_props($$props);
  const $$restProps = rest_props($$sanitized_props, ["class"]);
  push();
  let classes;
  let className = fallback($$props["class"], "");
  classes = classnames(className, "modal-footer");
  $$payload.out += `<div${spread_attributes({ ...$$restProps, class: clsx(classes) }, null)}><!---->`;
  slot($$payload, $$props, "default", {}, null);
  $$payload.out += `<!----></div>`;
  bind_props($$props, { class: className });
  pop();
}
const colorMode = writable(getInitialColorMode());
colorMode.subscribe((mode) => useColorMode(mode));
function getInitialColorMode() {
  var _a, _b, _c;
  const currentTheme = ((_a = globalThis.document) == null ? void 0 : _a.documentElement.getAttribute("data-bs-theme")) || "light";
  const prefersDarkMode = typeof ((_b = globalThis.window) == null ? void 0 : _b.matchMedia) === "function" ? (_c = globalThis.window) == null ? void 0 : _c.matchMedia("(prefers-color-scheme: dark)").matches : false;
  return currentTheme === "dark" || currentTheme === "auto" && prefersDarkMode ? "dark" : "light";
}
function useColorMode(element, mode) {
  var _a;
  let target = element;
  if (arguments.length === 1) {
    target = (_a = globalThis.document) == null ? void 0 : _a.documentElement;
    if (!target) {
      return;
    }
    mode = element;
    colorMode.update(() => mode);
  }
  target.setAttribute("data-bs-theme", mode);
}
const toggle = () => modal.show = !modal.show;
let modal = {
  type: "",
  message: "",
  closedCallback: null,
  confirmCallback: null,
  confirmCallbackPassData: null,
  show: false
};
function alert(message, closedCallback = null) {
  modal.type = "alert";
  modal.message = message;
  modal.closedCallback = closedCallback;
  modal.confirmCallbackPassData = null;
  modal.confirmCallbackPassDataPassData = null;
  modal.show = true;
}
function Modal_1($$payload) {
  Modal($$payload, {
    isOpen: modal.show,
    toggle,
    children: ($$payload2) => {
      ModalHeader($$payload2, {
        toggle,
        children: ($$payload3) => {
          $$payload3.out += `<!---->${escape_html(modal.type == "alert" ? "Alert" : "Confirmation")}`;
        },
        $$slots: { default: true }
      });
      $$payload2.out += `<!----> `;
      ModalBody($$payload2, {
        children: ($$payload3) => {
          $$payload3.out += `<!---->${escape_html(modal.message)}`;
        },
        $$slots: { default: true }
      });
      $$payload2.out += `<!----> `;
      ModalFooter($$payload2, {
        children: ($$payload3) => {
          if (modal.type == "confirm") {
            $$payload3.out += "<!--[-->";
            Button($$payload3, {
              color: "success",
              children: ($$payload4) => {
                $$payload4.out += `<!---->Confirm`;
              },
              $$slots: { default: true }
            });
          } else {
            $$payload3.out += "<!--[!-->";
          }
          $$payload3.out += `<!--]--> `;
          Button($$payload3, {
            color: "danger",
            children: ($$payload4) => {
              $$payload4.out += `<!---->Cancel`;
            },
            $$slots: { default: true }
          });
          $$payload3.out += `<!---->`;
        },
        $$slots: { default: true }
      });
      $$payload2.out += `<!---->`;
    },
    $$slots: { default: true }
  });
}
function setCsrfToken(csrfToken) {
}
function App($$payload, $$props) {
  push();
  var $$store_subs;
  let { children } = $$props;
  setCsrfToken(store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.csrf_token);
  if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.flash.success) {
    alert(store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.flash.success);
  }
  if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.flash.error) {
    alert(store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.flash.error);
  }
  const each_array = ensure_array_like(Object.entries(store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.nav));
  head($$payload, ($$payload2) => {
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.title) {
      $$payload2.out += "<!--[-->";
      $$payload2.title = `<title>${escape_html(store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.title)} | ${escape_html("Mensa")}</title>`;
    } else {
      $$payload2.out += "<!--[!-->";
      $$payload2.title = `<title>${escape_html("Mensa")} </title>`;
    }
    $$payload2.out += `<!--]-->`;
  });
  $$payload.out += `<header class="navbar navbar-expand-lg navbar-dark sticky-top bg-dark nav-pills"><nav class="container-xxl flex-wrap" aria-label="Main navigation"><button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#bdSidebar" aria-label="Toggle admin navigation"><span class="navbar-toggler-icon"></span></button> <a class="navbar-brand"${attr("href", route("index"))}>Mensa</a> <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bdNavbar" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button> <div class="collapse navbar-collapse" id="bdNavbar"><ul class="navbar-nav me-auto"><!--[-->`;
  for (let $$index = 0, $$length = each_array.length; $$index < $$length; $$index++) {
    let [id, item] = each_array[$$index];
    if (item.children) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="nav-item dropdown"><button class="nav-link dropdown-toggle"${attr("id", `dropdown${stringify(id)}`)} data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">${escape_html(item.name)}</button> `;
      NavDropdown_1($$payload, { items: item.children, id });
      $$payload.out += `<!----></li>`;
    } else {
      $$payload.out += "<!--[!-->";
      $$payload.out += `<li class="nav-item"><a${attr("href", item.url ?? "#")} class="nav-link">${escape_html(item.name)}</a></li>`;
    }
    $$payload.out += `<!--]-->`;
  }
  $$payload.out += `<!--]--></ul> <hr class="d-lg-none text-white-50"/> <ul class="navbar-nav">`;
  if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user) {
    $$payload.out += "<!--[-->";
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.hasProctorTests || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.length || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="nav-item"><a${attr("href", store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.hasProctorTests || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.length ? route("admin.index") : route("admin.admission-tests.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current().startsWith("admin.")
        }
      ]))}>Admin</a></li> <hr class="d-lg-none text-white-50"/>`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--> <li class="nav-item"><a${attr("href", route("profile.show"))}${attr_class(clsx([
      "nav-link",
      "align-items-center",
      { active: route().current(profile.show) }
    ]))}>Profile</a></li> <li class="nav-item"><a${attr("href", route("logout"))} class="nav-link align-items-center">Logout</a></li>`;
  } else {
    $$payload.out += "<!--[!-->";
    $$payload.out += `<li class="nav-item"><a${attr("href", route("login"))}${attr_class(clsx([
      "nav-link",
      "align-items-center",
      { active: route().current("login") }
    ]))}>Login</a></li> <li class="nav-item"><a${attr("href", route("register"))}${attr_class(clsx([
      "nav-link",
      "align-items-center",
      { active: route().current("register") }
    ]))}>Register</a></li>`;
  }
  $$payload.out += `<!--]--></ul></div></nav></header> <div${attr_class(clsx([
    "container-xxl",
    {
      "d-flex": route().current().startsWith("admin.")
    }
  ]))}>`;
  if (route().current().startsWith("admin.")) {
    $$payload.out += "<!--[-->";
    $$payload.out += `<aside class="offcanvas-lg offcanvas-start" tabindex="-1" id="bdSidebar" aria-labelledby="bdSidebarOffcanvasLabel"><div class="offcanvas-header"><h5 id="bdSidebarOffcanvasLabel">Admin</h5> <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#bdSidebar"></button></div> <nav class="offcanvas-body"><ul class="nav flex-column nav-pills">`;
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.length || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="nav-item"><a${attr("href", route("admin.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        { active: route().current("admin.index") }
      ]))}>Dashboard</a></li> `;
      if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("View:User") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
        $$payload.out += "<!--[-->";
        if (route().current("admin.users.show")) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavAdminUser" aria-controls="asideNavAdminUser" style="height: 0em"${attr_class(clsx([
            "nav-item",
            "accordion-button",
            {
              collapsed: !route().current().startsWith("admin.users.")
            }
          ]))}>Users</button> <ul id="asideNavAdminUser"${attr_class(clsx([
            "accordion-collapse",
            "collapse",
            {
              show: route().current().startsWith("admin.users.")
            }
          ]))}><li><a${attr("href", route("admin.users.index"))}${attr_class(clsx([
            "nav-link",
            "align-items-center",
            {
              active: route().current("admin.users.index")
            }
          ]))}>Index</a></li> `;
          if (route().current("admin.users.show")) {
            $$payload.out += "<!--[-->";
            $$payload.out += `<a${attr("href", route().current())} class="nav-link align-items-center active">Show</a>`;
          } else {
            $$payload.out += "<!--[!-->";
          }
          $$payload.out += `<!--]--></ul></li>`;
        } else {
          $$payload.out += "<!--[!-->";
          $$payload.out += `<li class="nav-item"><a${attr("href", route("admin.users.index"))}${attr_class(clsx([
            "nav-link",
            "align-items-center",
            {
              active: route().current("admin.users.index")
            }
          ]))}>Users</a></li>`;
        }
        $$payload.out += `<!--]-->`;
      } else {
        $$payload.out += "<!--[!-->";
      }
      $$payload.out += `<!--]--> <li class="nav-item"><a${attr("href", route("admin.team-types.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.team-types.index")
        }
      ]))}>Team Types</a></li> `;
      if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("View:User") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Edit:Permission") || route().current().startsWith("admin.teams.roles.") || ["admin.teams.show", "admin.teams.edit"].includes(route().current())) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavAdminTeam" aria-controls="asideNavAdminTeam" style="height: 0em"${attr_class(clsx([
          "nav-item",
          "accordion-button",
          {
            collapsed: !route().current().startsWith("admin.teams.")
          }
        ]))}>Teams</button> <ul id="asideNavAdminTeam"${attr_class(clsx([
          "accordion-collapse",
          "collapse",
          {
            show: route().current().startsWith("admin.teams.")
          }
        ]))}><li><a${attr("href", route("admin.teams.index"))}${attr_class(clsx([
          "nav-link",
          "align-items-center",
          {
            active: route().current("admin.teams.index")
          }
        ]))}>Index</a></li> `;
        if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Permission") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li><a${attr("href", route("admin.teams.create"))}${attr_class(clsx([
            "nav-link",
            "align-items-center",
            {
              active: route().current("admin.teams.create")
            }
          ]))}>Create</a></li>`;
        } else {
          $$payload.out += "<!--[!-->";
        }
        $$payload.out += `<!--]--> `;
        if (route().current().startsWith("admin.teams.roles.") || ["admin.teams.show", "admin.teams.edit"].includes(route().current())) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li><a${attr("href", route("admin.teams.show", { team: route().params.team }))}${attr_class(clsx([
            "nav-link",
            "align-items-center",
            {
              active: route().current("admin.teams.show")
            }
          ]))}>Show</a></li>`;
        } else {
          $$payload.out += "<!--[!-->";
        }
        $$payload.out += `<!--]--> `;
        if (route().current("admin.teams.edit")) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li><a${attr("href", route("admin.teams.edit", { team: route().params.team }))} class="nav-link align-items-center active">Edit</a></li>`;
        } else {
          $$payload.out += "<!--[!-->";
        }
        $$payload.out += `<!--]--> `;
        if (route().current("admin.teams.roles.create")) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li><a${attr("href", route("admin.teams.roles.create", { team: route().params.team }))} class="nav-link align-items-center active">Create Role</a></li>`;
        } else {
          $$payload.out += "<!--[!-->";
        }
        $$payload.out += `<!--]--> `;
        if (route().current("admin.teams.roles.edit")) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li><a${attr("href", route().current())} class="nav-link align-items-center active">Edit Role</a></li>`;
        } else {
          $$payload.out += "<!--[!-->";
        }
        $$payload.out += `<!--]--></ul></li>`;
      } else {
        $$payload.out += "<!--[!-->";
        $$payload.out += `<li class="nav-item"><a${attr("href", route("admin.teams.index"))}${attr_class(clsx([
          "nav-link",
          "align-items-center",
          {
            active: route().current("admin.teams.index")
          }
        ]))}>Teams</a></li>`;
      }
      $$payload.out += `<!--]--> <li class="nav-item"><a${attr("href", route("admin.modules.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.modules.index")
        }
      ]))}>Module</a></li> <li class="nav-item"><a${attr("href", route("admin.permissions.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.permissions.index")
        }
      ]))}>Permission</a></li>`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--> `;
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Admission Test") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavAdminAdmissionTestType" aria-controls="asideNavAdminAdmissionTestType" style="height: 0em"${attr_class(clsx([
        "nav-item",
        "accordion-button",
        {
          collapsed: !route().current().startsWith("admin.admission-test.types.")
        }
      ]))}>Admission Test Types</button> <ul id="asideNavAdminAdmissionTestType"${attr_class(clsx([
        "accordion-collapse",
        "collapse",
        {
          show: route().current("admin.admission-test.types.")
        }
      ]))}><li><a${attr("href", route("admin.admission-test.types.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.admission-test.types.index")
        }
      ]))}>Index</a></li> <li><a${attr("href", route("admin.admission-test.types.create"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.admission-test.types.create")
        }
      ]))}>Create</a></li> `;
      if (route().current("admin.admission-test.types.edit")) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<li><a${attr("href", route().current())} class="nav-link align-items-center active">Edit</a></li>`;
      } else {
        $$payload.out += "<!--[!-->";
      }
      $$payload.out += `<!--]--></ul></li> <li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavAdminAdmissionTestProduct" aria-controls="asideNavAdminAdmissionTestProduct" style="height: 0em"${attr_class(clsx([
        "nav-item",
        "accordion-button",
        {
          collapsed: !route().current().startsWith("admin.admission-test.products.")
        }
      ]))}>Admission Test Products</button> <ul id="asideNavAdminAdmissionTestProduct"${attr_class(clsx([
        "accordion-collapse",
        "collapse",
        {
          show: route().current().startsWith("admin.admission-test.products.")
        }
      ]))}><li><a${attr("href", route("admin.admission-test.products.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.admission-test.products.index")
        }
      ]))}>Index</a></li> <li><a${attr("href", route("admin.admission-test.products.create"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.admission-test.products.create")
        }
      ]))}>Create</a></li> `;
      if (route().current("admin.admission-test.products.show")) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<li><a${attr("href", route().current())} class="nav-link align-items-center active">Show</a></li>`;
      } else {
        $$payload.out += "<!--[!-->";
      }
      $$payload.out += `<!--]--></ul></li>`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--> `;
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.hasProctorTests || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Admission Test") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      if (!(store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Admission Test") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) && !route().current("admin.admission-tests.show")) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<li class="nav-item"><a${attr("href", route("admin.admission-tests.index"))}${attr_class(clsx([
          "nav-link",
          "align-items-center",
          {
            active: route().current("admin.admission-tests.index")
          }
        ]))}>Admission Tests</a></li>`;
      } else {
        $$payload.out += "<!--[!-->";
        $$payload.out += `<li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavAdminAdmissionTest" aria-controls="asideNavAdminAdmissionTest" style="height: 0em"${attr_class(clsx([
          "nav-item",
          "accordion-button",
          {
            collapsed: !route().current().startsWith("admin.admission-tests.")
          }
        ]))}>Admission Tests</button> <ul id="asideNavAdminAdmissionTest"${attr_class(clsx([
          "accordion-collapse",
          "collapse",
          {
            show: route().current().startsWith("admin.admission-tests.")
          }
        ]))}><li><a${attr("href", route("admin.admission-tests.index"))}${attr_class(clsx([
          "nav-link",
          "align-items-center",
          {
            active: route().current("admin.admission-tests.index")
          }
        ]))}>Index</a></li> `;
        if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Admission Test") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li><a${attr("href", route("admin.admission-tests.create"))}${attr_class(clsx([
            "nav-link",
            "align-items-center",
            {
              active: route().current("admin.admission-tests.create")
            }
          ]))}>Create</a></li>`;
        } else {
          $$payload.out += "<!--[!-->";
        }
        $$payload.out += `<!--]--> `;
        if (route().current("admin.admission-tests.show")) {
          $$payload.out += "<!--[-->";
          $$payload.out += `<li><a${attr("href", route().current())} class="nav-link align-items-center active">Show</a></li>`;
        } else {
          $$payload.out += "<!--[!-->";
        }
        $$payload.out += `<!--]--></ul></li>`;
      }
      $$payload.out += `<!--]-->`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--> `;
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Other Payment Gateway") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="nav-item"><a${attr("href", route("admin.other-payment-gateways.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.other-payment-gateways.index")
        }
      ]))}>Other Payment Gateway</a></li>`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--> `;
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Site Content") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      if (route().current("admin.site-contents.edit")) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavSiteContent" aria-controls="asideNavSiteContent" style="height: 0em" class="accordion-button">Site Content</button> <ul id="asideNavSiteContent" class="accordion-collapse collapse show"><li><a${attr("href", route("admin.site-contents.index"))} class="nav-link align-items-center">Index</a></li> <li><a${attr("href", route().current())} class="nav-link align-items-center active">Edit</a></li></ul></li>`;
      } else {
        $$payload.out += "<!--[!-->";
        $$payload.out += `<li class="nav-item"><a${attr("href", route("admin.site-contents.index"))}${attr_class(clsx([
          "nav-link",
          "align-items-center",
          {
            active: route().current("admin.site-contents.index")
          }
        ]))}>Site Content</a></li>`;
      }
      $$payload.out += `<!--]-->`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--> `;
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Custom Web Page") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavCustomWebPage" aria-controls="asideNavCustomWebPage" style="height: 0em"${attr_class(clsx([
        "nav-item",
        "accordion-button",
        {
          collapsed: !route().current().startsWith("admin.custom-web-pages.")
        }
      ]))}>Custom Web Pages</button> <ul id="asideNavCustomWebPage"${attr_class(clsx([
        "accordion-collapse",
        "collapse",
        {
          show: route().current().startsWith("admin.custom-web-pages.")
        }
      ]))}><li><a${attr("href", route("admin.custom-web-pages.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.custom-web-pages.index")
        }
      ]))}>Index</a></li> <li><a${attr("href", route("admin.custom-web-pages.create"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.custom-web-pages.create")
        }
      ]))}>Create</a></li> `;
      if (route().current("admin.custom-web-pages.edit")) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<li><a${attr("href", route().current())} class="nav-link align-items-center active">Edit</a></li>`;
      } else {
        $$payload.out += "<!--[!-->";
      }
      $$payload.out += `<!--]--></ul></li>`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--> `;
    if (store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.permissions.includes("Edit:Navigation Item") || store_get($$store_subs ?? ($$store_subs = {}), "$page", page).props.user.roles.includes("Super Administrator")) {
      $$payload.out += "<!--[-->";
      $$payload.out += `<li class="accordion"><button data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#asideNavNavigationItem" aria-controls="asideNavNavigationItem" style="height: 0em"${attr_class(clsx([
        "nav-item",
        "accordion-button",
        {
          collapsed: !route().current().startsWith("admin.navigation-items.")
        }
      ]))}>Navigation Items</button> <ul id="asideNavNavigationItem"${attr_class(clsx([
        "accordion-collapse",
        "collapse",
        {
          show: route().current().startsWith("admin.navigation-items.")
        }
      ]))}><li><a${attr("href", route("admin.navigation-items.index"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.navigation-items.index")
        }
      ]))}>Index</a></li> <li><a${attr("href", route("admin.navigation-items.create"))}${attr_class(clsx([
        "nav-link",
        "align-items-center",
        {
          active: route().current("admin.navigation-items.create")
        }
      ]))}>Create</a></li> `;
      if (route().current("admin.navigation-items.edit")) {
        $$payload.out += "<!--[-->";
        $$payload.out += `<li><a${attr("href", route().current())} class="nav-link align-items-center active">Edit</a></li>`;
      } else {
        $$payload.out += "<!--[!-->";
      }
      $$payload.out += `<!--]--></ul></li>`;
    } else {
      $$payload.out += "<!--[!-->";
    }
    $$payload.out += `<!--]--></ul></nav></aside>`;
  } else {
    $$payload.out += "<!--[!-->";
  }
  $$payload.out += `<!--]--> <main class="w-100">`;
  children($$payload);
  $$payload.out += `<!----></main></div> `;
  Modal_1($$payload);
  $$payload.out += `<!---->`;
  if ($$store_subs) unsubscribe_stores($$store_subs);
  pop();
}
createServer(
  (page2) => createInertiaApp({
    page: page2,
    resolve: (name) => {
      return { default: page2.default, layout: page2.layout || App };
    },
    setup({ App: App2, props }) {
      return render(App2, { props });
    }
  }),
  { cluster: true }
);
