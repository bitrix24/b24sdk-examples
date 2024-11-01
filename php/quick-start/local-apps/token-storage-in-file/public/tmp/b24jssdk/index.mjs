import { DateTime } from 'luxon';
import $protobuf from 'protobufjs/minimal';
import axios, { AxiosError } from 'axios';
import qs from 'qs';

var LoggerType = /* @__PURE__ */ ((LoggerType2) => {
  LoggerType2["desktop"] = "desktop";
  LoggerType2["log"] = "log";
  LoggerType2["info"] = "info";
  LoggerType2["warn"] = "warn";
  LoggerType2["error"] = "error";
  LoggerType2["trace"] = "trace";
  return LoggerType2;
})(LoggerType || {});
const styleCollection = /* @__PURE__ */ new Map();
styleCollection.set(
  "title",
  [
    "%c#title#",
    "color: #959ca4; font-style: italic; padding: 0 6px; border-top: 1px solid #ccc; border-left: 1px solid #ccc; border-bottom: 1px solid #ccc"
  ]
);
styleCollection.set(
  "desktop" /* desktop */,
  [
    `%cDESKTOP`,
    "color: white; font-style: italic; background-color: #29619b; padding: 0 6px; border: 1px solid #29619b"
  ]
);
styleCollection.set(
  "log" /* log */,
  [
    `%cLOG`,
    "color: #2a323b; font-style: italic; background-color: #ccc; padding: 0 6px; border: 1px solid #ccc"
  ]
);
styleCollection.set(
  "info" /* info */,
  [
    `%cINFO`,
    "color: #fff; font-style: italic; background-color: #6b7f96; padding: 0 6px; border: 1px solid #6b7f96"
  ]
);
styleCollection.set(
  "warn" /* warn */,
  [
    `%cWARNING`,
    "color: #f0a74f; font-style: italic; padding: 0 6px; border: 1px solid #f0a74f"
  ]
);
styleCollection.set(
  "error" /* error */,
  [
    `%cERROR`,
    "color: white; font-style: italic; background-color: #8a3232; padding: 0 6px; border: 1px solid #8a3232"
  ]
);
styleCollection.set(
  "trace" /* trace */,
  [
    `%cTRACE`,
    "color: #2a323b; font-style: italic; background-color: #ccc; padding: 0 6px; border: 1px solid #ccc"
  ]
);
class LoggerBrowser {
  #title;
  #types = {
    desktop: true,
    log: false,
    info: false,
    warn: false,
    error: true,
    trace: true
  };
  static build(title, isDevelopment = false) {
    const logger = new LoggerBrowser(title);
    if (isDevelopment) {
      logger.enable("log" /* log */);
      logger.enable("info" /* info */);
      logger.enable("warn" /* warn */);
    }
    return logger;
  }
  constructor(title) {
    this.#title = title;
  }
  // region Styles ////
  #getStyle(type) {
    const resultText = [];
    const resultStyle = [];
    if (styleCollection.has("title")) {
      const styleTitle = styleCollection.get("title");
      if (styleTitle[0]) {
        resultText.push(styleTitle[0].replace("#title#", this.#title));
        resultStyle.push(styleTitle[1] || "");
      }
    }
    if (styleCollection.has(type)) {
      const styleBadge = styleCollection.get(type);
      if (styleBadge[0]) {
        resultText.push(styleBadge[0]);
        resultStyle.push(styleBadge[1] || "");
      }
    }
    return [resultText.join(""), ...resultStyle];
  }
  // endregion ////
  // region Config ////
  setConfig(types) {
    for (const type in types) {
      this.#types[type] = types[type];
    }
  }
  enable(type) {
    if (typeof this.#types[type] === "undefined") {
      return false;
    }
    this.#types[type] = true;
    return true;
  }
  disable(type) {
    if (typeof this.#types[type] === "undefined") {
      return false;
    }
    this.#types[type] = false;
    return true;
  }
  isEnabled(type) {
    return this.#types[type];
  }
  // endregion ////
  // region Functions ////
  desktop(...params) {
    if (this.isEnabled("desktop" /* desktop */)) {
      console.log(...[...this.#getStyle("desktop" /* desktop */), ...params]);
    }
  }
  log(...params) {
    if (this.isEnabled("log" /* log */)) {
      console.log(...[...this.#getStyle("log" /* log */), ...params]);
    }
  }
  info(...params) {
    if (this.isEnabled("info" /* info */)) {
      console.info(...[...this.#getStyle("info" /* info */), ...params]);
    }
  }
  warn(...params) {
    if (this.isEnabled("warn" /* warn */)) {
      console.warn(...[...this.#getStyle("warn" /* warn */), ...params]);
    }
  }
  error(...params) {
    if (this.isEnabled("error" /* error */)) {
      console.error(...[...this.#getStyle("error" /* error */), ...params]);
    }
  }
  trace(...params) {
    if (this.isEnabled("trace" /* trace */)) {
      console.trace(...[...this.#getStyle("trace" /* trace */), ...params]);
    }
  }
  // endregion ////
}

var DataType = /* @__PURE__ */ ((DataType2) => {
  DataType2["undefined"] = "undefined";
  DataType2["any"] = "any";
  DataType2["integer"] = "integer";
  DataType2["boolean"] = "boolean";
  DataType2["double"] = "double";
  DataType2["date"] = "date";
  DataType2["datetime"] = "datetime";
  DataType2["string"] = "string";
  DataType2["text"] = "text";
  DataType2["file"] = "file";
  DataType2["array"] = "array";
  DataType2["object"] = "object";
  DataType2["user"] = "user";
  DataType2["location"] = "location";
  DataType2["crmCategory"] = "crm_category";
  DataType2["crmStatus"] = "crm_status";
  DataType2["crmCurrency"] = "crm_currency";
  return DataType2;
})(DataType || {});

const objectCtorString = Function.prototype.toString.call(Object);
class TypeManager {
  getTag(value) {
    return Object.prototype.toString.call(value);
  }
  /**
   * Checks that value is string
   * @param value
   * @return {boolean}
   *
   * @memo get from pull.client.Utils
   */
  isString(value) {
    return value === "" ? true : value ? typeof value === "string" || value instanceof String : false;
  }
  /**
   * Returns true if a value is not empty string
   * @param value
   * @returns {boolean}
   */
  isStringFilled(value) {
    return this.isString(value) && value !== "";
  }
  /**
   * Checks that value is function
   * @param value
   * @return {boolean}
   *
   * @memo get from pull.client.Utils
   */
  isFunction(value) {
    return value === null ? false : typeof value === "function" || value instanceof Function;
  }
  /**
   * Checks that value is object
   * @param value
   * @return {boolean}
   */
  isObject(value) {
    return !!value && (typeof value === "object" || typeof value === "function");
  }
  /**
   * Checks that value is object like
   * @param value
   * @return {boolean}
   */
  isObjectLike(value) {
    return !!value && typeof value === "object";
  }
  /**
   * Checks that value is plain object
   * @param value
   * @return {boolean}
   */
  isPlainObject(value) {
    if (!this.isObjectLike(value) || this.getTag(value) !== "[object Object]") {
      return false;
    }
    const proto = Object.getPrototypeOf(value);
    if (proto === null) {
      return true;
    }
    const ctor = proto.hasOwnProperty("constructor") && proto.constructor;
    return typeof ctor === "function" && Function.prototype.toString.call(ctor) === objectCtorString;
  }
  isJsonRpcRequest(value) {
    return typeof value === "object" && value && "jsonrpc" in value && this.isStringFilled(value.jsonrpc) && "method" in value && this.isStringFilled(value.method);
  }
  isJsonRpcResponse(value) {
    return typeof value === "object" && value && "jsonrpc" in value && this.isStringFilled(value.jsonrpc) && "id" in value && ("result" in value || "error" in value);
  }
  /**
   * Checks that value is boolean
   * @param value
   * @return {boolean}
   */
  isBoolean(value) {
    return value === true || value === false;
  }
  /**
   * Checks that value is number
   * @param value
   * @return {boolean}
   */
  isNumber(value) {
    return !Number.isNaN(value) && typeof value === "number";
  }
  /**
   * Checks that value is integer
   * @param value
   * @return {boolean}
   */
  isInteger(value) {
    return this.isNumber(value) && value % 1 === 0;
  }
  /**
   * Checks that value is float
   * @param value
   * @return {boolean}
   */
  isFloat(value) {
    return this.isNumber(value) && !this.isInteger(value);
  }
  /**
   * Checks that value is nil
   * @param value
   * @return {boolean}
   */
  isNil(value) {
    return value === null || value === void 0;
  }
  /**
   * Checks that value is array
   * @param value
   * @return {boolean}
   */
  isArray(value) {
    return !this.isNil(value) && Array.isArray(value);
  }
  /**
   * Returns true if a value is an array, and it has at least one element
   * @param value
   * @returns {boolean}
   */
  isArrayFilled(value) {
    return this.isArray(value) && value.length > 0;
  }
  /**
   * Checks that value is array like
   * @param value
   * @return {boolean}
   */
  isArrayLike(value) {
    return !this.isNil(value) && !this.isFunction(value) && value.length > -1 && value.length <= Number.MAX_SAFE_INTEGER;
  }
  /**
   * Checks that value is Date
   * @param value
   * @return {boolean}
   */
  isDate(value) {
    return this.isObjectLike(value) && this.getTag(value) === "[object Date]";
  }
  /**
   * Checks that is DOM node
   * @param value
   * @return {boolean}
   */
  isDomNode(value) {
    return this.isObjectLike(value) && !this.isPlainObject(value) && "nodeType" in value;
  }
  /**
   * Checks that value is element node
   * @param value
   * @return {boolean}
   */
  isElementNode(value) {
    return this.isDomNode(value) && value.nodeType === Node.ELEMENT_NODE;
  }
  /**
   * Checks that value is text node
   * @param value
   * @return {boolean}
   */
  isTextNode(value) {
    return this.isDomNode(value) && value.nodeType === Node.TEXT_NODE;
  }
  /**
   * Checks that value is Map
   * @param value
   * @return {boolean}
   */
  isMap(value) {
    return this.isObjectLike(value) && this.getTag(value) === "[object Map]";
  }
  /**
   * Checks that value is Set
   * @param value
   * @return {boolean}
   */
  isSet(value) {
    return this.isObjectLike(value) && this.getTag(value) === "[object Set]";
  }
  /**
   * Checks that value is WeakMap
   * @param value
   * @return {boolean}
   */
  isWeakMap(value) {
    return this.isObjectLike(value) && this.getTag(value) === "[object WeakMap]";
  }
  /**
   * Checks that value is WeakSet
   * @param value
   * @return {boolean}
   */
  isWeakSet(value) {
    return this.isObjectLike(value) && this.getTag(value) === "[object WeakSet]";
  }
  /**
   * Checks that value is prototype
   * @param value
   * @return {boolean}
   */
  isPrototype(value) {
    return (typeof (value && value.constructor) === "function" && value.constructor.prototype || Object.prototype) === value;
  }
  /**
   * Checks that value is regexp
   * @param value
   * @return {boolean}
   */
  isRegExp(value) {
    return this.isObjectLike(value) && this.getTag(value) === "[object RegExp]";
  }
  /**
   * Checks that value is null
   * @param value
   * @return {boolean}
   */
  isNull(value) {
    return value === null;
  }
  /**
   * Checks that value is undefined
   * @param value
   * @return {boolean}
   */
  isUndefined(value) {
    return typeof value === "undefined";
  }
  /**
   * Checks that value is ArrayBuffer
   * @param value
   * @return {boolean}
   */
  isArrayBuffer(value) {
    return this.isObjectLike(value) && this.getTag(value) === "[object ArrayBuffer]";
  }
  /**
   * Checks that value is typed array
   * @param value
   * @return {boolean}
   */
  isTypedArray(value) {
    const regExpTypedTag = /^\[object (?:Float(?:32|64)|(?:Int|Uint)(?:8|16|32)|Uint8Clamped)]$/;
    return this.isObjectLike(value) && regExpTypedTag.test(this.getTag(value));
  }
  /**
   * Checks that value is Blob
   * @param value
   * @return {boolean}
   */
  isBlob(value) {
    return this.isObjectLike(value) && this.isNumber(value.size) && this.isString(value.type) && this.isFunction(value.slice);
  }
  /**
   * Checks that value is File
   * @param value
   * @return {boolean}
   */
  isFile(value) {
    return this.isBlob(value) && this.isString(value.name) && (this.isNumber(value.lastModified) || this.isObjectLike(value.lastModifiedDate));
  }
  /**
   * Checks that value is FormData
   * @param value
   * @return {boolean}
   */
  isFormData(value) {
    return value instanceof FormData;
  }
  clone(obj, bCopyObj = true) {
    let _obj, i, l;
    if (obj === null) {
      return null;
    }
    if (this.isDomNode(obj)) {
      _obj = obj.cloneNode(bCopyObj);
    } else if (typeof obj == "object") {
      if (this.isArray(obj)) {
        _obj = [];
        for (i = 0, l = obj.length; i < l; i++) {
          if (typeof obj[i] == "object" && bCopyObj) {
            _obj[i] = this.clone(obj[i], bCopyObj);
          } else {
            _obj[i] = obj[i];
          }
        }
      } else {
        _obj = {};
        if (obj.constructor) {
          if (this.isDate(obj)) {
            _obj = new Date(obj);
          } else {
            _obj = new obj.constructor();
          }
        }
        for (i in obj) {
          if (!obj.hasOwnProperty(i)) {
            continue;
          }
          if (typeof obj[i] === "object" && bCopyObj) {
            _obj[i] = this.clone(obj[i], bCopyObj);
          } else {
            _obj[i] = obj[i];
          }
        }
      }
    } else {
      _obj = obj;
    }
    return _obj;
  }
}
const Type = new TypeManager();

const _state = {};
let getRandomValues;
const randoms8 = new Uint8Array(16);
const byteToHex = [];
for (let i = 0; i < 256; ++i) {
  byteToHex.push((i + 256).toString(16).slice(1));
}
function updateV7State(state, now, randoms) {
  state.msecs ??= -Infinity;
  state.seq ??= 0;
  if (now > state.msecs) {
    state.seq = randoms[6] << 23 | randoms[7] << 16 | randoms[8] << 8 | randoms[9];
    state.msecs = now;
  } else {
    state.seq = state.seq + 1 | 0;
    if (state.seq === 0) {
      state.msecs++;
    }
  }
  return state;
}
function v7Bytes(randoms, msecs, seq, buf, offset = 0) {
  if (!buf) {
    buf = new Uint8Array(16);
    offset = 0;
  }
  msecs ??= Date.now();
  seq ??= randoms[6] * 127 << 24 | randoms[7] << 16 | randoms[8] << 8 | randoms[9];
  buf[offset++] = msecs / 1099511627776 & 255;
  buf[offset++] = msecs / 4294967296 & 255;
  buf[offset++] = msecs / 16777216 & 255;
  buf[offset++] = msecs / 65536 & 255;
  buf[offset++] = msecs / 256 & 255;
  buf[offset++] = msecs & 255;
  buf[offset++] = 112 | seq >>> 28 & 15;
  buf[offset++] = seq >>> 20 & 255;
  buf[offset++] = 128 | seq >>> 14 & 63;
  buf[offset++] = seq >>> 6 & 255;
  buf[offset++] = seq << 2 & 255 | randoms[10] & 3;
  buf[offset++] = randoms[11];
  buf[offset++] = randoms[12];
  buf[offset++] = randoms[13];
  buf[offset++] = randoms[14];
  buf[offset++] = randoms[15];
  return buf;
}
function unsafeStringify(arr, offset = 0) {
  return (byteToHex[arr[offset]] + byteToHex[arr[offset + 1]] + byteToHex[arr[offset + 2]] + byteToHex[arr[offset + 3]] + "-" + byteToHex[arr[offset + 4]] + byteToHex[arr[offset + 5]] + "-" + byteToHex[arr[offset + 6]] + byteToHex[arr[offset + 7]] + "-" + byteToHex[arr[offset + 8]] + byteToHex[arr[offset + 9]] + "-" + byteToHex[arr[offset + 10]] + byteToHex[arr[offset + 11]] + byteToHex[arr[offset + 12]] + byteToHex[arr[offset + 13]] + byteToHex[arr[offset + 14]] + byteToHex[arr[offset + 15]]).toLowerCase();
}
function uuidv7() {
  let bytes;
  let buf = void 0;
  let offset = void 0;
  const now = Date.now();
  if (!getRandomValues) {
    if (typeof crypto === "undefined" || !crypto.getRandomValues) {
      throw new Error(
        "crypto.getRandomValues() not supported."
      );
    }
    getRandomValues = crypto.getRandomValues.bind(crypto);
  }
  const randoms = getRandomValues(randoms8);
  updateV7State(_state, now, randoms);
  bytes = v7Bytes(randoms, _state.msecs, _state.seq, buf, offset);
  return unsafeStringify(bytes);
}

const reEscape = /[&<>'"]/g;
const reUnescape = /&(?:amp|#38|lt|#60|gt|#62|apos|#39|quot|#34)/g;
const escapeEntities = {
  "&": "&amp",
  "<": "&lt",
  ">": "&gt",
  "'": "&#39",
  '"': "&quot"
};
const unescapeEntities = {
  "&amp": "&",
  "&#38": "&",
  "&lt": "<",
  "&#60": "<",
  "&gt": ">",
  "&#62": ">",
  "&apos": "'",
  "&#39": "'",
  "&quot": '"',
  "&#34": '"'
};
class TextManager {
  getRandom(length = 8) {
    return [...Array(length)].map(() => (~~(Math.random() * 36)).toString(36)).join("");
  }
  /**
   * Generates UUID
   */
  getUniqId() {
    return "xxxxxxxx-xlsx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
      const r = Math.random() * 16 | 0;
      const v = c === "x" ? r : r & 3 | 8;
      return v.toString(16);
    });
  }
  /**
   * Generate uuid v7
   * @return {string}
   */
  getUuidRfc4122() {
    return uuidv7();
  }
  /**
   * Encodes all unsafe entities
   * @param {string} value
   * @return {string}
   */
  encode(value) {
    if (Type.isString(value)) {
      return value.replace(reEscape, (item) => escapeEntities[item]);
    }
    return value;
  }
  /**
   * Decodes all encoded entities
   * @param {string} value
   * @return {string}
   */
  decode(value) {
    if (Type.isString(value)) {
      return value.replace(reUnescape, (item) => unescapeEntities[item]);
    }
    return value;
  }
  toNumber(value) {
    const parsedValue = Number.parseFloat(value);
    if (Type.isNumber(parsedValue)) {
      return parsedValue;
    }
    return 0;
  }
  toInteger(value) {
    return this.toNumber(Number.parseInt(value, 10));
  }
  toBoolean(value, trueValues = []) {
    const transformedValue = Type.isString(value) ? value.toLowerCase() : value;
    return ["true", "y", "1", 1, true, ...trueValues].includes(transformedValue);
  }
  toCamelCase(str) {
    if (!Type.isStringFilled(str)) {
      return str;
    }
    const regex = /[-_\s]+(.)?/g;
    if (!regex.test(str)) {
      return str.match(/^[A-Z]+$/) ? str.toLowerCase() : str[0].toLowerCase() + str.slice(1);
    }
    str = str.toLowerCase();
    str = str.replace(regex, (_match, letter) => letter ? letter.toUpperCase() : "");
    return str[0].toLowerCase() + str.substring(1);
  }
  toPascalCase(str) {
    if (!Type.isStringFilled(str)) {
      return str;
    }
    return this.capitalize(this.toCamelCase(str));
  }
  toKebabCase(str) {
    if (!Type.isStringFilled(str)) {
      return str;
    }
    const matches = str.match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g);
    if (!matches) {
      return str;
    }
    return matches.map((x) => x.toLowerCase()).join("-");
  }
  capitalize(str) {
    if (!Type.isStringFilled(str)) {
      return str;
    }
    return str[0].toUpperCase() + str.substring(1);
  }
  numberFormat(number, decimals = 0, decPoint = ".", thousandsSep = ",") {
    const n = !Number.isFinite(number) ? 0 : number;
    const fractionDigits = !Number.isFinite(decimals) ? 0 : Math.abs(decimals);
    const toFixedFix = (n2, fractionDigits2) => {
      const k = Math.pow(10, fractionDigits2);
      return Math.round(n2 * k) / k;
    };
    let s = (fractionDigits ? toFixedFix(n, fractionDigits) : Math.round(n)).toString().split(".");
    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousandsSep);
    }
    if ((s[1] || "").length < fractionDigits) {
      s[1] = s[1] || "";
      s[1] += new Array(fractionDigits - s[1].length + 1).join("0");
    }
    return s.join(decPoint);
  }
  /**
   * Convert string to DateTime from ISO 8601 or self template
   *
   * @param {string} dateString
   * @param {string} template
   * @param opts
   * @returns {DateTime}
   *
   * @link https://moment.github.io/luxon/#/parsing?id=parsing-technical-formats
   */
  toDateTime(dateString, template, opts) {
    if (!(typeof template === "undefined") && Type.isStringFilled(template)) {
      return DateTime.fromFormat(
        dateString,
        template,
        opts
      );
    }
    return DateTime.fromISO(dateString, opts);
  }
  getDateForLog() {
    const now = DateTime.now();
    return now.toFormat("y-MM-dd HH:mm:ss");
  }
  buildQueryString(params) {
    let result = "";
    for (let key in params) {
      if (!params.hasOwnProperty(key)) {
        continue;
      }
      const value = params[key];
      if (Type.isArray(value)) {
        value.forEach((valueElement, index) => {
          result += encodeURIComponent(key + "[" + index + "]") + "=" + encodeURIComponent(valueElement) + "&";
        });
      } else {
        result += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
      }
    }
    if (result.length > 0) {
      result = result.substring(0, result.length - 1);
    }
    return result;
  }
}
const Text = new TextManager();

const UA = navigator?.userAgent.toLowerCase() || "?";
class BrowserManager {
  isOpera() {
    return UA.includes("opera");
  }
  isIE() {
    return "attachEvent" in document && !this.isOpera();
  }
  isIE6() {
    return UA.includes("msie 6");
  }
  isIE7() {
    return UA.includes("msie 7");
  }
  isIE8() {
    return UA.includes("msie 8");
  }
  isIE9() {
    return "documentMode" in document && document.documentMode >= 9;
  }
  isIE10() {
    return "documentMode" in document && document.documentMode >= 10;
  }
  isSafari() {
    return UA.includes("safari") && !UA.includes("chrome");
  }
  isFirefox() {
    return UA.includes("firefox");
  }
  isChrome() {
    return UA.includes("chrome");
  }
  detectIEVersion() {
    if (this.isOpera() || this.isSafari() || this.isFirefox() || this.isChrome()) {
      return -1;
    }
    let rv = -1;
    if (
      // @ts-ignore ////
      !!window.MSStream && !window.ActiveXObject && "ActiveXObject" in window
    ) {
      rv = 11;
    } else if (this.isIE10()) {
      rv = 10;
    } else if (this.isIE9()) {
      rv = 9;
    } else if (this.isIE()) {
      rv = 8;
    }
    if (rv === -1 || rv === 8) {
      if (navigator.appName === "Microsoft Internet Explorer") {
        const re = new RegExp("MSIE ([0-9]+[.0-9]*)");
        const res = navigator.userAgent.match(re);
        if (Type.isArrayLike(res) && res.length > 0) {
          rv = parseFloat(res[1]);
        }
      }
      if (navigator.appName === "Netscape") {
        rv = 11;
        const re = new RegExp("Trident/.*rv:([0-9]+[.0-9]*)");
        if (re.exec(navigator.userAgent) != null) {
          const res = navigator.userAgent.match(re);
          if (Type.isArrayLike(res) && res.length > 0) {
            rv = parseFloat(res[1]);
          }
        }
      }
    }
    return rv;
  }
  isIE11() {
    return this.detectIEVersion() >= 11;
  }
  isMac() {
    return UA.includes("macintosh");
  }
  isWin() {
    return UA.includes("windows");
  }
  isLinux() {
    return UA.includes("linux") && !this.isAndroid();
  }
  isAndroid() {
    return UA.includes("android");
  }
  isIPad() {
    return UA.includes("ipad;") || this.isMac() && this.isTouchDevice();
  }
  isIPhone() {
    return UA.includes("iphone;");
  }
  isIOS() {
    return this.isIPad() || this.isIPhone();
  }
  isMobile() {
    return this.isIPhone() || this.isIPad() || this.isAndroid() || UA.includes("mobile") || UA.includes("touch");
  }
  isRetina() {
    return (window.devicePixelRatio && window.devicePixelRatio >= 2) === true;
  }
  isTouchDevice() {
    return "ontouchstart" in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
  }
  isDoctype(target) {
    const doc = target || document;
    if (doc.compatMode) {
      return doc.compatMode === "CSS1Compat";
    }
    return doc.documentElement && doc.documentElement.clientHeight;
  }
  isLocalStorageSupported() {
    try {
      localStorage.setItem("test", "test");
      localStorage.removeItem("test");
      return true;
    } catch (error) {
      return false;
    }
  }
  detectAndroidVersion() {
    const re = new RegExp("Android ([0-9]+[.0-9]*)");
    if (re.exec(navigator.userAgent) != null) {
      const res = navigator.userAgent.match(re);
      if (Type.isArrayLike(res) && res.length > 0) {
        return parseFloat(res[1]);
      }
    }
    return 0;
  }
}
const Browser = new BrowserManager();

const RestrictionManagerParamsBase = {
  sleep: 1e3,
  speed: 1e-3,
  amount: 30
};
const RestrictionManagerParamsForEnterprise = {
  sleep: 600,
  speed: 0.01,
  amount: 30 * 5
};

var EnumCrmEntityType = /* @__PURE__ */ ((EnumCrmEntityType2) => {
  EnumCrmEntityType2["undefined"] = "UNDEFINED";
  EnumCrmEntityType2["lead"] = "CRM_LEAD";
  EnumCrmEntityType2["deal"] = "CRM_DEAL";
  EnumCrmEntityType2["contact"] = "CRM_CONTACT";
  EnumCrmEntityType2["company"] = "CRM_COMPANY";
  EnumCrmEntityType2["oldInvoice"] = "CRM_INVOICE";
  EnumCrmEntityType2["invoice"] = "CRM_SMART_INVOICE";
  EnumCrmEntityType2["quote"] = "CRM_QUOTE";
  EnumCrmEntityType2["requisite"] = "CRM_REQUISITE";
  return EnumCrmEntityType2;
})(EnumCrmEntityType || {});
var EnumCrmEntityTypeId = /* @__PURE__ */ ((EnumCrmEntityTypeId2) => {
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["undefined"] = 0] = "undefined";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["lead"] = 1] = "lead";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["deal"] = 2] = "deal";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["contact"] = 3] = "contact";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["company"] = 4] = "company";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["oldInvoice"] = 5] = "oldInvoice";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["invoice"] = 31] = "invoice";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["quote"] = 7] = "quote";
  EnumCrmEntityTypeId2[EnumCrmEntityTypeId2["requisite"] = 8] = "requisite";
  return EnumCrmEntityTypeId2;
})(EnumCrmEntityTypeId || {});

var LoadDataType = /* @__PURE__ */ ((LoadDataType2) => {
  LoadDataType2["App"] = "app";
  LoadDataType2["Profile"] = "profile";
  LoadDataType2["Currency"] = "currency";
  LoadDataType2["AppOptions"] = "appOptions";
  LoadDataType2["UserOptions"] = "userOptions";
  return LoadDataType2;
})(LoadDataType || {});
const EnumAppStatus = {
  // free ////
  Free: "F",
  // demo version ////
  Demo: "D",
  // trial version (limited time) ////
  Trial: "T",
  // paid application ////
  Paid: "P",
  // local application ////
  Local: "L",
  // subscription application ////
  Subscription: "S"
};
const StatusDescriptions = {
  [EnumAppStatus.Free]: "Free",
  [EnumAppStatus.Demo]: "Demo",
  [EnumAppStatus.Trial]: "Trial",
  [EnumAppStatus.Paid]: "Paid",
  [EnumAppStatus.Local]: "Local",
  [EnumAppStatus.Subscription]: "Subscription"
};
const TypeSpecificUrl = {
  MainSettings: "MainSettings",
  UfList: "UfList",
  UfPage: "UfPage"
};
var TypeOption = /* @__PURE__ */ ((TypeOption2) => {
  TypeOption2["NotSet"] = "notSet";
  TypeOption2["JsonArray"] = "jsonArray";
  TypeOption2["JsonObject"] = "jsonObject";
  TypeOption2["FloatVal"] = "float";
  TypeOption2["IntegerVal"] = "integer";
  TypeOption2["BoolYN"] = "boolYN";
  TypeOption2["StringVal"] = "string";
  return TypeOption2;
})(TypeOption || {});

var ConnectionType = /* @__PURE__ */ ((ConnectionType2) => {
  ConnectionType2["Undefined"] = "undefined";
  ConnectionType2["WebSocket"] = "webSocket";
  ConnectionType2["LongPolling"] = "longPolling";
  return ConnectionType2;
})(ConnectionType || {});
var LsKeys = /* @__PURE__ */ ((LsKeys2) => {
  LsKeys2["PullConfig"] = "bx-pull-config";
  LsKeys2["WebsocketBlocked"] = "bx-pull-websocket-blocked";
  LsKeys2["LongPollingBlocked"] = "bx-pull-longpolling-blocked";
  LsKeys2["LoggingEnabled"] = "bx-pull-logging-enabled";
  return LsKeys2;
})(LsKeys || {});
var PullStatus = /* @__PURE__ */ ((PullStatus2) => {
  PullStatus2["Online"] = "online";
  PullStatus2["Offline"] = "offline";
  PullStatus2["Connecting"] = "connect";
  return PullStatus2;
})(PullStatus || {});
var SenderType = /* @__PURE__ */ ((SenderType2) => {
  SenderType2[SenderType2["Unknown"] = 0] = "Unknown";
  SenderType2[SenderType2["Client"] = 1] = "Client";
  SenderType2[SenderType2["Backend"] = 2] = "Backend";
  return SenderType2;
})(SenderType || {});
var SubscriptionType = /* @__PURE__ */ ((SubscriptionType2) => {
  SubscriptionType2["Server"] = "server";
  SubscriptionType2["Client"] = "client";
  SubscriptionType2["Online"] = "online";
  SubscriptionType2["Status"] = "status";
  SubscriptionType2["Revision"] = "revision";
  return SubscriptionType2;
})(SubscriptionType || {});
var CloseReasons = /* @__PURE__ */ ((CloseReasons2) => {
  CloseReasons2[CloseReasons2["NORMAL_CLOSURE"] = 1e3] = "NORMAL_CLOSURE";
  CloseReasons2[CloseReasons2["SERVER_DIE"] = 1001] = "SERVER_DIE";
  CloseReasons2[CloseReasons2["CONFIG_REPLACED"] = 3e3] = "CONFIG_REPLACED";
  CloseReasons2[CloseReasons2["CHANNEL_EXPIRED"] = 3001] = "CHANNEL_EXPIRED";
  CloseReasons2[CloseReasons2["SERVER_RESTARTED"] = 3002] = "SERVER_RESTARTED";
  CloseReasons2[CloseReasons2["CONFIG_EXPIRED"] = 3003] = "CONFIG_EXPIRED";
  CloseReasons2[CloseReasons2["MANUAL"] = 3004] = "MANUAL";
  CloseReasons2[CloseReasons2["STUCK"] = 3005] = "STUCK";
  CloseReasons2[CloseReasons2["WRONG_CHANNEL_ID"] = 4010] = "WRONG_CHANNEL_ID";
  return CloseReasons2;
})(CloseReasons || {});
var SystemCommands = /* @__PURE__ */ ((SystemCommands2) => {
  SystemCommands2["CHANNEL_EXPIRE"] = "CHANNEL_EXPIRE";
  SystemCommands2["CONFIG_EXPIRE"] = "CONFIG_EXPIRE";
  SystemCommands2["SERVER_RESTART"] = "SERVER_RESTART";
  return SystemCommands2;
})(SystemCommands || {});
var ServerMode = /* @__PURE__ */ ((ServerMode2) => {
  ServerMode2["Shared"] = "shared";
  ServerMode2["Personal"] = "personal";
  return ServerMode2;
})(ServerMode || {});
const ListRpcError = {
  Parse: { code: -32700, message: "Parse error" },
  InvalidRequest: { code: -32600, message: "Invalid Request" },
  MethodNotFound: { code: -32601, message: "Method not found" },
  InvalidParams: { code: -32602, message: "Invalid params" },
  Internal: { code: -32603, message: "Internal error" }
};
var RpcMethod = /* @__PURE__ */ ((RpcMethod2) => {
  RpcMethod2["Publish"] = "publish";
  RpcMethod2["GetUsersLastSeen"] = "getUsersLastSeen";
  RpcMethod2["Ping"] = "ping";
  RpcMethod2["ListChannels"] = "listChannels";
  RpcMethod2["SubscribeStatusChange"] = "subscribeStatusChange";
  RpcMethod2["UnsubscribeStatusChange"] = "unsubscribeStatusChange";
  return RpcMethod2;
})(RpcMethod || {});

class Result {
  _errorCollection;
  _data;
  constructor() {
    this._errorCollection = /* @__PURE__ */ new Set();
    this._data = null;
  }
  /**
   * Getter for the `isSuccess` property.
   * Checks if the `_errorCollection` is empty to determine success.
   *
   * @returns Whether the operation resulted in success (no errors).
   */
  get isSuccess() {
    return this._errorCollection.size < 1;
  }
  /**
   * Sets the data associated with the result.
   *
   * @param data The data to be stored in the result.
   * @returns The current Result object for chaining methods.
   */
  setData(data) {
    this._data = data;
    return this;
  }
  /**
   * Retrieves the data associated with the result.
   *
   * @returns The data stored in the result, if any.
   */
  getData() {
    return this._data;
  }
  /**
   * Adds an error message or Error object to the result.
   *
   * @param error The error message or Error object to be added.
   * @returns The current Result object for chaining methods.
   */
  addError(error) {
    if (error instanceof Error) {
      this._errorCollection.add(error);
    } else {
      this._errorCollection.add(new Error(error.toString()));
    }
    return this;
  }
  /**
   * Adds multiple errors to the result in a single call.
   *
   * @param errors An array of errors or strings that will be converted to errors.
   * @returns The current Result object for chaining methods.
   */
  addErrors(errors) {
    errors.forEach((error) => {
      if (error instanceof Error) {
        this._errorCollection.add(error);
      } else {
        this._errorCollection.add(new Error(error.toString()));
      }
    });
    return this;
  }
  /**
   * Retrieves an iterator for the errors collected in the result.
   * @returns An iterator over the stored Error objects.
   */
  getErrors() {
    return this._errorCollection.values();
  }
  /**
   * Retrieves an array of error messages from the collected errors.
   *
   * @returns An array of strings representing the error messages. Each string
   *          contains the message of a corresponding error object.
   */
  getErrorMessages() {
    return Array.from(this.getErrors()).map((error) => error.message);
  }
  /**
   * Converts the Result object to a string.
   *
   * @returns {string} Returns a string representation of the result operation
   */
  toString() {
    if (this.isSuccess) {
      return `Result (success): data: ${JSON.stringify(this._data)}`;
    }
    return `Result (failure): errors: ${this.getErrorMessages().join(", ")}`;
  }
}

class AjaxError extends Error {
  cause;
  _status;
  _answerError;
  constructor(params) {
    const message = `${params.answerError.error}${!!params.answerError.errorDescription ? ": " + params.answerError.errorDescription : ""}`;
    super(message);
    this.cause = params.cause || null;
    this.name = this.constructor.name;
    this._status = params.status;
    this._answerError = params.answerError;
  }
  get answerError() {
    return this._answerError;
  }
  get status() {
    return this._status;
  }
  set status(status) {
    this._status = status;
  }
  toString() {
    return `${this.answerError.error}${!!this.answerError.errorDescription ? ": " + this.answerError.errorDescription : ""} (${this.status})`;
  }
}

class AjaxResult extends Result {
  _status;
  _query;
  _data;
  constructor(answer, query, status) {
    super();
    this._data = answer;
    this._query = structuredClone(query);
    this._status = status;
    if (typeof this._data.error !== "undefined") {
      let error = typeof this._data.error === "string" ? this._data : this._data.error;
      this.addError(new AjaxError({
        status: this._status,
        answerError: {
          error: error.error || "",
          errorDescription: error.error_description || ""
        }
      }));
    }
  }
  // @ts-ignore
  setData(data) {
    throw new Error("AjaxResult not support setData()");
  }
  getData() {
    return this._data;
  }
  isMore() {
    return Type.isNumber(this._data?.next);
  }
  getTotal() {
    return Text.toInteger(this._data?.total);
  }
  getStatus() {
    return this._status;
  }
  getQuery() {
    return this._query;
  }
  async getNext(http) {
    if (this.isMore() && this.isSuccess) {
      this._query.start = parseInt(this._data?.next);
      return http.call(
        this._query.method,
        this._query.params,
        this._query.start
      );
    }
    return Promise.resolve(false);
  }
}

class RestrictionManager {
  #params;
  #lastDecrement;
  #currentAmount;
  _logger = null;
  constructor() {
    this.#params = RestrictionManagerParamsBase;
    this.#currentAmount = 0;
    this.#lastDecrement = 0;
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  get params() {
    return { ...this.#params };
  }
  set params(params) {
    this.#params = params;
    this.getLogger().log(
      `new restriction manager params`,
      params
    );
  }
  check(hash = "") {
    return new Promise((resolve) => {
      this.#decrementStorage();
      if (this.#checkStorage()) {
        this.getLogger().log(`>> no sleep >>> ${hash}`, this.#getStorageStatus());
        this.#incrementStorage();
        return resolve(null);
      } else {
        const sleep = (callback) => {
          this.getLogger().info(`>> go sleep >>> ${hash}`, this.#getStorageStatus());
          setTimeout(() => {
            callback();
          }, this.#params.sleep);
        };
        const wait = () => {
          this.#decrementStorage();
          if (!this.#checkStorage()) {
            sleep(wait);
          } else {
            this.getLogger().info(`<< stop sleep <<< ${hash}`, this.#getStorageStatus());
            this.#incrementStorage();
            return resolve(null);
          }
        };
        sleep(wait);
      }
    });
  }
  #getStorageStatus() {
    return `${this.#currentAmount.toFixed(4)} from ${this.#params.amount}`;
  }
  #decrementStorage() {
    if (this.#lastDecrement > 0) {
      this.#currentAmount -= ((/* @__PURE__ */ new Date()).valueOf() - this.#lastDecrement) * this.#params.speed;
      if (this.#currentAmount < 0) {
        this.#currentAmount = 0;
      }
    }
    this.#lastDecrement = (/* @__PURE__ */ new Date()).valueOf();
  }
  #incrementStorage() {
    this.#currentAmount++;
  }
  #checkStorage() {
    return this.#currentAmount < this.#params.amount;
  }
}

const DEFAULT_REQUEST_ID_HEADER_FIELD_NAME = "X-Request-ID";
const DEFAULT_QUERY_STRING_PARAMETER_NAME = "bx24_request_id";
class DefaultRequestIdGenerator {
  getQueryStringParameterName() {
    return DEFAULT_QUERY_STRING_PARAMETER_NAME;
  }
  generate() {
    return Text.getUuidRfc4122();
  }
  getRequestId() {
    return this.generate();
  }
  getHeaderFieldName() {
    return DEFAULT_REQUEST_ID_HEADER_FIELD_NAME;
  }
}

class Http {
  #clientAxios;
  #authActions;
  #restrictionManager;
  #requestIdGenerator;
  _logger = null;
  #logTag = "";
  constructor(baseURL, authActions, options) {
    this.#clientAxios = axios.create({
      baseURL,
      ...options ?? {}
    });
    this.#authActions = authActions;
    this.#restrictionManager = new RestrictionManager();
    this.#requestIdGenerator = new DefaultRequestIdGenerator();
  }
  setLogger(logger) {
    this._logger = logger;
    this.#restrictionManager.setLogger(this.getLogger());
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  setRestrictionManagerParams(params) {
    this.#restrictionManager.params = params;
  }
  getRestrictionManagerParams() {
    return this.#restrictionManager.params;
  }
  setLogTag(logTag) {
    this.#logTag = logTag;
  }
  clearLogTag() {
    this.#logTag = "";
  }
  async batch(calls, isHaltOnError = true) {
    const isArrayMode = Array.isArray(calls);
    let cmd = isArrayMode ? [] : {};
    let cnt = 0;
    const processRow = (row, index) => {
      let method = null;
      let params = null;
      if (Array.isArray(row)) {
        method = row[0];
        params = row[1];
      } else if (!!row.method) {
        method = row.method;
        params = row.params;
      }
      if (!!method) {
        cnt++;
        let data = method + "?" + qs.stringify(params);
        if (isArrayMode || Array.isArray(cmd)) {
          cmd.push(data);
        } else {
          cmd[index] = data;
        }
      }
    };
    if (isArrayMode) {
      calls.forEach((item, index) => processRow(item, index));
    } else {
      Object.entries(calls).forEach(([index, item]) => processRow(item, index));
    }
    if (cnt < 1) {
      return Promise.resolve(new Result());
    }
    return this.call(
      "batch",
      {
        halt: isHaltOnError ? 1 : 0,
        cmd
      }
    ).then((response) => {
      const responseResult = response.getData().result;
      const results = isArrayMode ? [] : {};
      const processResponse = (row, index) => {
        if (
          // @ts-ignore
          typeof responseResult.result[index] !== "undefined" || typeof responseResult.result_error[index] !== "undefined"
        ) {
          let q = row.split("?");
          let data = new AjaxResult(
            {
              // @ts-ignore
              result: typeof responseResult.result[index] !== "undefined" ? responseResult.result[index] : {},
              // @ts-ignore
              error: responseResult?.result_error[index] || void 0,
              // @ts-ignore
              total: responseResult.result_total[index],
              // @ts-ignore
              next: responseResult.result_next[index]
            },
            {
              method: q[0] || "",
              params: qs.parse(q[1] || ""),
              start: 0
            },
            response.getStatus()
          );
          if (isArrayMode || Array.isArray(results)) {
            results.push(data);
          } else {
            results[index] = data;
          }
        }
      };
      if (Array.isArray(cmd)) {
        cmd.forEach((item, index) => processResponse(item, index));
      } else {
        Object.entries(cmd).forEach(([index, item]) => processResponse(
          item,
          index
        ));
      }
      let dataResult;
      const initError = (result2) => {
        return new AjaxError({
          status: 0,
          answerError: {
            error: result2.getErrorMessages().join("; "),
            errorDescription: `batch ${result2.getQuery().method}: ${qs.stringify(result2.getQuery().params, { encode: false })}`
          },
          cause: result2.getErrors().next().value
        });
      };
      const result = new Result();
      if (isArrayMode || Array.isArray(results)) {
        dataResult = [];
        for (let data of results) {
          if (data.getStatus() !== 200 || !data.isSuccess) {
            const error = initError(data);
            if (!isHaltOnError && !data.isSuccess) {
              result.addError(error);
              continue;
            }
            return Promise.reject(error);
          }
          dataResult.push(data.getData().result);
        }
      } else {
        dataResult = {};
        for (let key of Object.keys(results)) {
          let data = results[key];
          if (data.getStatus() !== 200 || !data.isSuccess) {
            const error = initError(data);
            if (!isHaltOnError && !data.isSuccess) {
              result.addError(error);
              continue;
            }
            return Promise.reject(error);
          }
          dataResult[key] = data.getData().result;
        }
      }
      result.setData(dataResult);
      return Promise.resolve(result);
    });
  }
  /**
   * Calling the RestApi function
   *
   * If we get a problem with authorization, we make 1 attempt to update the access token
   *
   * @param method
   * @param params
   * @param start
   */
  async call(method, params, start = 0) {
    let authData = this.#authActions.getAuthData();
    if (authData === false) {
      authData = await this.#authActions.refreshAuth();
    }
    await this.#restrictionManager.check();
    return this.#clientAxios.post(
      this.#prepareMethod(method),
      this.#prepareParams(authData, params, start)
    ).then(
      (response) => {
        const payload = response.data;
        return Promise.resolve({
          status: response.status,
          payload
        });
      },
      async (problem) => {
        let answerError = {
          error: problem?.code || 0,
          errorDescription: problem?.message || ""
        };
        if (problem instanceof AxiosError && problem.response && problem.response.data) {
          const response = problem.response.data;
          answerError = {
            error: response.error,
            errorDescription: response.error_description
          };
        }
        const problemError = new AjaxError({
          status: problem.response?.status || 0,
          answerError,
          cause: problem
        });
        if (problemError.status === 401) {
          if ([
            "expired_token",
            "invalid_token"
          ].includes(problemError.answerError.error)) {
            this.getLogger().info(`refreshAuth >> ${problemError.answerError.error} >>>`);
            authData = await this.#authActions.refreshAuth();
            await this.#restrictionManager.check();
            return this.#clientAxios.post(
              this.#prepareMethod(method),
              this.#prepareParams(authData, params, start)
            ).then(
              async (response) => {
                const payload = response.data;
                return Promise.resolve({
                  status: response.status,
                  payload
                });
              },
              async (problem2) => {
                let answerError2 = {
                  error: problem2?.code || 0,
                  errorDescription: problem2?.message || ""
                };
                if (problem2 instanceof AxiosError && problem2.response && problem2.response.data) {
                  const response = problem2.response.data;
                  answerError2 = {
                    error: response.error,
                    errorDescription: response.error_description
                  };
                }
                const problemError2 = new AjaxError({
                  status: problem2.response?.status || 0,
                  answerError: answerError2,
                  cause: problem2
                });
                return Promise.reject(problemError2);
              }
            );
          }
        }
        return Promise.reject(problemError);
      }
    ).then((response) => {
      const result = new AjaxResult(
        response.payload,
        {
          method,
          params,
          start
        },
        response.status
      );
      return Promise.resolve(result);
    });
  }
  /**
   * Processes function parameters and adds authorization
   *
   * @param authData
   * @param params
   * @param start
   *
   * @private
   */
  #prepareParams(authData, params, start = 0) {
    let result = Object.assign({}, params);
    if (this.#logTag.length > 0) {
      result.logTag = this.#logTag;
    }
    result[this.#requestIdGenerator.getQueryStringParameterName()] = this.#requestIdGenerator.getRequestId();
    if (!!result.data) {
      if (!!result.data.start) {
        delete result.data.start;
      }
    }
    if (authData.refresh_token !== "hook") {
      result.auth = authData.access_token;
    }
    result.start = start;
    return result;
  }
  /**
   * Makes the function name safe and adds json format
   *
   * @param method
   * @private
   */
  #prepareMethod(method) {
    return `${encodeURIComponent(method)}.json`;
  }
}

class AbstractB24 {
  static batchSize = 50;
  _isInit = false;
  _http = null;
  _logger = null;
  // region Init ////
  constructor() {
    this._isInit = false;
  }
  /**
   * @inheritDoc
   */
  get isInit() {
    return this._isInit;
  }
  async init() {
    this._isInit = true;
    return Promise.resolve();
  }
  destroy() {
  }
  setLogger(logger) {
    this._logger = logger;
    this.getHttpClient().setLogger(this.getLogger());
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  /**
   * @inheritDoc
   */
  callMethod(method, params, start) {
    return this.getHttpClient().call(
      method,
      params || {},
      start || 0
    );
  }
  /**
   * @inheritDoc
   */
  async callListMethod(method, params = {}, progress = null, customKeyForResult = null) {
    const result = new Result();
    if (!!progress) {
      progress(0);
    }
    return this.callMethod(
      method,
      params,
      0
    ).then(async (response) => {
      let list = [];
      let resultData;
      if (null !== customKeyForResult) {
        resultData = response.getData().result[customKeyForResult];
      } else {
        resultData = response.getData().result;
      }
      list = list.concat(resultData);
      if (response.isMore()) {
        let responseLoop = response;
        while (true) {
          responseLoop = await responseLoop.getNext(this.getHttpClient());
          if (responseLoop === false) {
            break;
          }
          let resultData2 = void 0;
          if (null !== customKeyForResult) {
            resultData2 = responseLoop.getData().result[customKeyForResult];
          } else {
            resultData2 = responseLoop.getData().result;
          }
          list = list.concat(resultData2);
          if (!!progress) {
            let total = responseLoop.getTotal();
            progress(total > 0 ? Math.round(100 * list.length / total) : 100);
          }
        }
      }
      result.setData(list);
      if (!!progress) {
        progress(100);
      }
      return Promise.resolve(result);
    });
  }
  /**
   * @inheritDoc
   */
  async *fetchListMethod(method, params = {}, idKey = "ID", customKeyForResult = null) {
    params.order = params.order || {};
    params.filter = params.filter || {};
    params.start = -1;
    let moreIdKey = `>${idKey}`;
    params.order[idKey] = "ASC";
    params.filter[moreIdKey] = 0;
    do {
      let result = await this.callMethod(method, params, params.start);
      let data = void 0;
      if (null !== customKeyForResult) {
        data = result.getData().result[customKeyForResult];
      } else {
        data = result.getData().result;
      }
      if (data.length === 0) {
        break;
      }
      yield data;
      if (data.length < AbstractB24.batchSize) {
        break;
      }
      const value = data[data.length - 1];
      if (value && idKey in value) {
        params.filter[moreIdKey] = value[idKey];
      }
    } while (true);
  }
  /**
   * @inheritDoc
   */
  async callBatch(calls, isHaltOnError = true) {
    return this.getHttpClient().batch(
      calls,
      isHaltOnError
    );
  }
  chunkArray(array, chunkSize = 50) {
    const result = [];
    for (let i = 0; i < array.length; i += chunkSize) {
      const chunk = array.slice(i, i + chunkSize);
      result.push(chunk);
    }
    return result;
  }
  /**
   * @inheritDoc
   */
  async callBatchByChunk(calls, isHaltOnError = true) {
    const result = new Result();
    const data = [];
    const chunks = this.chunkArray(calls, AbstractB24.batchSize);
    try {
      for (const chunkRequest of chunks) {
        const response = await this.callBatch(chunkRequest, isHaltOnError);
        data.push(...response.getData());
      }
    } catch (error) {
      return Promise.reject(error);
    }
    return Promise.resolve(result.setData(data));
  }
  // endregion ////
  // region Tools ////
  /**
   * @inheritDoc
   */
  getHttpClient() {
    if (!this.isInit || null === this._http) {
      throw new Error(`Http not init`);
    }
    return this._http;
  }
  /**
   * Returns settings for http connection
   * @protected
   */
  _getHttpOptions() {
    return null;
  }
  /**
   * Generates an object not initialized error
   * @protected
   */
  _ensureInitialized() {
    if (!this._isInit) {
      throw new Error("B24 not initialized");
    }
  }
  // endregion ////
}

var B24LangList = /* @__PURE__ */ ((B24LangList2) => {
  B24LangList2["en"] = "en";
  B24LangList2["de"] = "de";
  B24LangList2["la"] = "la";
  B24LangList2["br"] = "br";
  B24LangList2["fr"] = "fr";
  B24LangList2["it"] = "it";
  B24LangList2["pl"] = "pl";
  B24LangList2["ru"] = "ru";
  B24LangList2["ua"] = "ua";
  B24LangList2["tr"] = "tr";
  B24LangList2["sc"] = "sc";
  B24LangList2["tc"] = "tc";
  B24LangList2["ja"] = "ja";
  B24LangList2["vn"] = "vn";
  B24LangList2["id"] = "id";
  B24LangList2["ms"] = "ms";
  B24LangList2["th"] = "th";
  B24LangList2["ar"] = "ar";
  return B24LangList2;
})(B24LangList || {});

const useScrollSize = () => {
  return {
    scrollWidth: Math.max(
      document.documentElement.scrollWidth,
      document.documentElement.offsetWidth
    ),
    scrollHeight: Math.max(
      document.documentElement.scrollHeight,
      document.documentElement.offsetHeight
    )
  };
};

class FormatterNumbers {
  static isInternalConstructing = false;
  static instance = null;
  _defLocale = null;
  constructor() {
    if (!FormatterNumbers.isInternalConstructing) {
      throw new TypeError("FormatterNumber is not constructable");
    }
    FormatterNumbers.isInternalConstructing = false;
  }
  /**
   * @return FormatterNumbers
   */
  static getInstance() {
    if (!FormatterNumbers.instance) {
      FormatterNumbers.isInternalConstructing = true;
      FormatterNumbers.instance = new FormatterNumbers();
    }
    return FormatterNumbers.instance;
  }
  setDefLocale(locale) {
    this._defLocale = locale;
  }
  format(value, locale) {
    let formatter;
    if (typeof locale === "undefined" || !Type.isStringFilled(locale)) {
      locale = Type.isStringFilled(this._defLocale) ? this._defLocale || "en" : navigator?.language;
    }
    if (Number.isInteger(value)) {
      formatter = new Intl.NumberFormat(
        locale,
        {
          minimumFractionDigits: 0,
          maximumFractionDigits: 0
        }
      );
    } else {
      formatter = new Intl.NumberFormat(
        locale,
        {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }
      );
    }
    let result = formatter.format(value);
    if (locale.includes("ru")) {
      result = result.replace(",", ".");
    }
    return result;
  }
}

class IbanSpecification {
  /**
   * the code of the country
   */
  countryCode;
  /**
   * the length of the IBAN
   */
  length;
  /**
   * the structure of the underlying BBAN (for validation and formatting)
   */
  structure;
  /**
   * an example valid IBAN
   */
  example;
  _cachedRegex = null;
  constructor(countryCode, length, structure, example) {
    this.countryCode = countryCode;
    this.length = length;
    this.structure = structure;
    this.example = example;
  }
  /**
   * Check if the passed iban is valid according to this specification.
   *
   * @param {String} iban the iban to validate
   * @returns {boolean} true if valid, false otherwise
   */
  isValid(iban) {
    return this.length === iban.length && this.countryCode === iban.slice(0, 2) && this._regex().test(iban.slice(4)) && this._iso7064Mod9710(this._iso13616Prepare(iban)) == 1;
  }
  /**
   * Convert the passed IBAN to a country-specific BBAN.
   *
   * @param iban the IBAN to convert
   * @param separator the separator to use between BBAN blocks
   * @returns {string} the BBAN
   */
  toBBAN(iban, separator) {
    return (this._regex().exec(iban.slice(4) || "") || []).slice(1).join(separator);
  }
  /**
   * Convert the passed BBAN to an IBAN for this country specification.
   * Please note that <i>"generation of the IBAN shall be the exclusive responsibility of the bank/branch servicing the account"</i>.
   * This method implements the preferred algorithm described in http://en.wikipedia.org/wiki/International_Bank_Account_Number#Generating_IBAN_check_digits
   *
   * @param bban the BBAN to convert to IBAN
   * @returns {string} the IBAN
   */
  fromBBAN(bban) {
    if (!this.isValidBBAN(bban)) {
      throw new Error("Invalid BBAN");
    }
    const remainder = this._iso7064Mod9710(
      this._iso13616Prepare(
        this.countryCode + "00" + bban
      )
    );
    const checkDigit = ("0" + (98 - remainder)).slice(-2);
    return this.countryCode + checkDigit + bban;
  }
  /**
   * Check of the passed BBAN is valid.
   * This function only checks the format of the BBAN (length and matching the letetr/number specs) but does not
   * verify the check digit.
   *
   * @param bban the BBAN to validate
   * @returns {boolean} true if the passed bban is a valid BBAN according to this specification, false otherwise
   */
  isValidBBAN(bban) {
    return this.length - 4 === bban.length && this._regex().test(bban);
  }
  /**
   * Lazy-loaded regex (parse the structure and construct the regular expression the first time we need it for validation)
   */
  _regex() {
    if (null === this._cachedRegex) {
      this._cachedRegex = this._parseStructure(this.structure);
    }
    return this._cachedRegex;
  }
  /**
   * Parse the BBAN structure used to configure each IBAN Specification and returns a matching regular expression.
   * A structure is composed of blocks of 3 characters (one letter and 2 digits). Each block represents
   * a logical group in the typical representation of the BBAN. For each group, the letter indicates which characters
   * are allowed in this group and the following 2-digits number tells the length of the group.
   *
   * @param {string} structure the structure to parse
   * @returns {RegExp}
   */
  _parseStructure(structure) {
    const regex = (structure.match(/(.{3})/g) || []).map((block) => {
      let format;
      const pattern = block.slice(0, 1);
      const repeats = parseInt(block.slice(1), 10);
      switch (pattern) {
        case "A":
          format = "0-9A-Za-z";
          break;
        case "B":
          format = "0-9A-Z";
          break;
        case "C":
          format = "A-Za-z";
          break;
        case "F":
          format = "0-9";
          break;
        case "L":
          format = "a-z";
          break;
        case "U":
          format = "A-Z";
          break;
        case "W":
          format = "0-9a-z";
          break;
      }
      return "([" + format + "]{" + repeats + "})";
    });
    return new RegExp("^" + regex.join("") + "$");
  }
  /**
   * Prepare an IBAN for mod 97 computation by moving the first 4 chars to the end and transforming the letters to
   * numbers (A = 10, B = 11, ..., Z = 35), as specified in ISO13616.
   *
   * @param {string} iban the IBAN
   * @returns {string} the prepared IBAN
   */
  _iso13616Prepare(iban) {
    const A = "A".charCodeAt(0);
    const Z = "Z".charCodeAt(0);
    iban = iban.toUpperCase();
    iban = iban.substring(4) + iban.substring(0, 4);
    return iban.split("").map((n) => {
      const code = n.charCodeAt(0);
      if (code >= A && code <= Z) {
        return (code - A + 10).toString();
      } else {
        return n;
      }
    }).join("");
  }
  /**
   * Calculates the MOD 97 10 of the passed IBAN as specified in ISO7064.
   *
   * @param iban
   * @returns {number}
   */
  _iso7064Mod9710(iban) {
    let remainder = iban;
    let block;
    while (remainder.length > 2) {
      block = remainder.slice(0, 9);
      remainder = parseInt(block, 10) % 97 + remainder.slice(block.length);
    }
    return parseInt(remainder, 10) % 97;
  }
}
class FormatterIban {
  static isInternalConstructing = false;
  static instance = null;
  _countries;
  // region Init ////
  constructor() {
    if (!FormatterIban.isInternalConstructing) {
      throw new TypeError("FormatterIban is not constructable");
    }
    FormatterIban.isInternalConstructing = false;
    this._countries = /* @__PURE__ */ new Map();
  }
  /**
   * @return FormatterIban
   */
  static getInstance() {
    if (!FormatterIban.instance) {
      FormatterIban.isInternalConstructing = true;
      FormatterIban.instance = new FormatterIban();
    }
    return FormatterIban.instance;
  }
  addSpecification(IBAN) {
    this._countries.set(
      IBAN.countryCode,
      IBAN
    );
  }
  // endregion ////
  // region IBAN ////
  /**
   * Check if an IBAN is valid.
   *
   * @param {String} iban the IBAN to validate.
   * @returns {boolean} true if the passed IBAN is valid, false otherwise
   */
  isValid(iban) {
    if (!this._isString(iban)) {
      return false;
    }
    iban = this.electronicFormat(iban);
    const countryCode = iban.slice(0, 2);
    if (!this._countries.has(countryCode)) {
      throw new Error(`No country with code ${countryCode}`);
    }
    let countryStructure = this._countries.get(countryCode);
    return !!countryStructure && countryStructure.isValid(iban);
  }
  printFormat(iban, separator) {
    if (typeof separator == "undefined") {
      separator = " ";
    }
    const EVERY_FOUR_CHARS = /(.{4})(?!$)/g;
    return this.electronicFormat(iban).replace(EVERY_FOUR_CHARS, "$1" + separator);
  }
  electronicFormat(iban) {
    const NON_ALPHANUM = /[^a-zA-Z0-9]/g;
    return iban.replace(NON_ALPHANUM, "").toUpperCase();
  }
  // endregion ////
  // region BBAN ////
  /**
   * Convert an IBAN to a BBAN.
   *
   * @param iban
   * @param {String} [separator] the separator to use between the blocks of the BBAN, defaults to ' '
   * @returns {string|*}
   */
  toBBAN(iban, separator) {
    if (typeof separator == "undefined") {
      separator = " ";
    }
    iban = this.electronicFormat(iban);
    const countryCode = iban.slice(0, 2);
    if (!this._countries.has(countryCode)) {
      throw new Error(`No country with code ${countryCode}`);
    }
    let countryStructure = this._countries.get(countryCode);
    if (!countryStructure) {
      throw new Error(`No country with code ${countryCode}`);
    }
    return countryStructure.toBBAN(
      iban,
      separator
    );
  }
  /**
   * Convert the passed BBAN to an IBAN for this country specification.
   * Please note that <i>"generation of the IBAN shall be the exclusive responsibility of the bank/branch servicing the account"</i>.
   * This method implements the preferred algorithm described in http://en.wikipedia.org/wiki/International_Bank_Account_Number#Generating_IBAN_check_digits
   *
   * @param countryCode the country of the BBAN
   * @param bban the BBAN to convert to IBAN
   * @returns {string} the IBAN
   */
  fromBBAN(countryCode, bban) {
    if (!this._countries.has(countryCode)) {
      throw new Error(`No country with code ${countryCode}`);
    }
    let countryStructure = this._countries.get(countryCode);
    if (!countryStructure) {
      throw new Error(`No country with code ${countryCode}`);
    }
    return countryStructure.fromBBAN(
      this.electronicFormat(bban)
    );
  }
  /**
   * Check the validity of the passed BBAN.
   *
   * @param countryCode the country of the BBAN
   * @param bban the BBAN to check the validity of
   */
  isValidBBAN(countryCode, bban) {
    if (!this._isString(bban)) {
      return false;
    }
    if (!this._countries.has(countryCode)) {
      throw new Error(`No country with code ${countryCode}`);
    }
    let countryStructure = this._countries.get(countryCode);
    return !!countryStructure && countryStructure.isValidBBAN(
      this.electronicFormat(bban)
    );
  }
  // endregion ////
  // region Tools ////
  _isString(value) {
    return typeof value == "string" || value instanceof String;
  }
  // endregion ////
}

const useFormatter = () => {
  const formatterNumber = FormatterNumbers.getInstance();
  const formatterIban = FormatterIban.getInstance();
  formatterIban.addSpecification(new IbanSpecification("AD", 24, "F04F04A12", "AD1200012030200359100100"));
  formatterIban.addSpecification(new IbanSpecification("AE", 23, "F03F16", "AE070331234567890123456"));
  formatterIban.addSpecification(new IbanSpecification("AL", 28, "F08A16", "AL47212110090000000235698741"));
  formatterIban.addSpecification(new IbanSpecification("AT", 20, "F05F11", "AT611904300234573201"));
  formatterIban.addSpecification(new IbanSpecification("AZ", 28, "U04A20", "AZ21NABZ00000000137010001944"));
  formatterIban.addSpecification(new IbanSpecification("BA", 20, "F03F03F08F02", "BA391290079401028494"));
  formatterIban.addSpecification(new IbanSpecification("BE", 16, "F03F07F02", "BE68539007547034"));
  formatterIban.addSpecification(new IbanSpecification("BG", 22, "U04F04F02A08", "BG80BNBG96611020345678"));
  formatterIban.addSpecification(new IbanSpecification("BH", 22, "U04A14", "BH67BMAG00001299123456"));
  formatterIban.addSpecification(new IbanSpecification("BR", 29, "F08F05F10U01A01", "BR9700360305000010009795493P1"));
  formatterIban.addSpecification(new IbanSpecification("BY", 28, "A04F04A16", "BY13NBRB3600900000002Z00AB00"));
  formatterIban.addSpecification(new IbanSpecification("CH", 21, "F05A12", "CH9300762011623852957"));
  formatterIban.addSpecification(new IbanSpecification("CR", 22, "F04F14", "CR72012300000171549015"));
  formatterIban.addSpecification(new IbanSpecification("CY", 28, "F03F05A16", "CY17002001280000001200527600"));
  formatterIban.addSpecification(new IbanSpecification("CZ", 24, "F04F06F10", "CZ6508000000192000145399"));
  formatterIban.addSpecification(new IbanSpecification("DE", 22, "F08F10", "DE89370400440532013000"));
  formatterIban.addSpecification(new IbanSpecification("DK", 18, "F04F09F01", "DK5000400440116243"));
  formatterIban.addSpecification(new IbanSpecification("DO", 28, "U04F20", "DO28BAGR00000001212453611324"));
  formatterIban.addSpecification(new IbanSpecification("EE", 20, "F02F02F11F01", "EE382200221020145685"));
  formatterIban.addSpecification(new IbanSpecification("EG", 29, "F04F04F17", "EG800002000156789012345180002"));
  formatterIban.addSpecification(new IbanSpecification("ES", 24, "F04F04F01F01F10", "ES9121000418450200051332"));
  formatterIban.addSpecification(new IbanSpecification("FI", 18, "F06F07F01", "FI2112345600000785"));
  formatterIban.addSpecification(new IbanSpecification("FO", 18, "F04F09F01", "FO6264600001631634"));
  formatterIban.addSpecification(new IbanSpecification("FR", 27, "F05F05A11F02", "FR1420041010050500013M02606"));
  formatterIban.addSpecification(new IbanSpecification("GB", 22, "U04F06F08", "GB29NWBK60161331926819"));
  formatterIban.addSpecification(new IbanSpecification("GE", 22, "U02F16", "GE29NB0000000101904917"));
  formatterIban.addSpecification(new IbanSpecification("GI", 23, "U04A15", "GI75NWBK000000007099453"));
  formatterIban.addSpecification(new IbanSpecification("GL", 18, "F04F09F01", "GL8964710001000206"));
  formatterIban.addSpecification(new IbanSpecification("GR", 27, "F03F04A16", "GR1601101250000000012300695"));
  formatterIban.addSpecification(new IbanSpecification("GT", 28, "A04A20", "GT82TRAJ01020000001210029690"));
  formatterIban.addSpecification(new IbanSpecification("HR", 21, "F07F10", "HR1210010051863000160"));
  formatterIban.addSpecification(new IbanSpecification("HU", 28, "F03F04F01F15F01", "HU42117730161111101800000000"));
  formatterIban.addSpecification(new IbanSpecification("IE", 22, "U04F06F08", "IE29AIBK93115212345678"));
  formatterIban.addSpecification(new IbanSpecification("IL", 23, "F03F03F13", "IL620108000000099999999"));
  formatterIban.addSpecification(new IbanSpecification("IS", 26, "F04F02F06F10", "IS140159260076545510730339"));
  formatterIban.addSpecification(new IbanSpecification("IT", 27, "U01F05F05A12", "IT60X0542811101000000123456"));
  formatterIban.addSpecification(new IbanSpecification("IQ", 23, "U04F03A12", "IQ98NBIQ850123456789012"));
  formatterIban.addSpecification(new IbanSpecification("JO", 30, "A04F22", "JO15AAAA1234567890123456789012"));
  formatterIban.addSpecification(new IbanSpecification("KW", 30, "U04A22", "KW81CBKU0000000000001234560101"));
  formatterIban.addSpecification(new IbanSpecification("KZ", 20, "F03A13", "KZ86125KZT5004100100"));
  formatterIban.addSpecification(new IbanSpecification("LB", 28, "F04A20", "LB62099900000001001901229114"));
  formatterIban.addSpecification(new IbanSpecification("LC", 32, "U04F24", "LC07HEMM000100010012001200013015"));
  formatterIban.addSpecification(new IbanSpecification("LI", 21, "F05A12", "LI21088100002324013AA"));
  formatterIban.addSpecification(new IbanSpecification("LT", 20, "F05F11", "LT121000011101001000"));
  formatterIban.addSpecification(new IbanSpecification("LU", 20, "F03A13", "LU280019400644750000"));
  formatterIban.addSpecification(new IbanSpecification("LV", 21, "U04A13", "LV80BANK0000435195001"));
  formatterIban.addSpecification(new IbanSpecification("MC", 27, "F05F05A11F02", "MC5811222000010123456789030"));
  formatterIban.addSpecification(new IbanSpecification("MD", 24, "U02A18", "MD24AG000225100013104168"));
  formatterIban.addSpecification(new IbanSpecification("ME", 22, "F03F13F02", "ME25505000012345678951"));
  formatterIban.addSpecification(new IbanSpecification("MK", 19, "F03A10F02", "MK07250120000058984"));
  formatterIban.addSpecification(new IbanSpecification("MR", 27, "F05F05F11F02", "MR1300020001010000123456753"));
  formatterIban.addSpecification(new IbanSpecification("MT", 31, "U04F05A18", "MT84MALT011000012345MTLCAST001S"));
  formatterIban.addSpecification(new IbanSpecification("MU", 30, "U04F02F02F12F03U03", "MU17BOMM0101101030300200000MUR"));
  formatterIban.addSpecification(new IbanSpecification("NL", 18, "U04F10", "NL91ABNA0417164300"));
  formatterIban.addSpecification(new IbanSpecification("NO", 15, "F04F06F01", "NO9386011117947"));
  formatterIban.addSpecification(new IbanSpecification("PK", 24, "U04A16", "PK36SCBL0000001123456702"));
  formatterIban.addSpecification(new IbanSpecification("PL", 28, "F08F16", "PL61109010140000071219812874"));
  formatterIban.addSpecification(new IbanSpecification("PS", 29, "U04A21", "PS92PALS000000000400123456702"));
  formatterIban.addSpecification(new IbanSpecification("PT", 25, "F04F04F11F02", "PT50000201231234567890154"));
  formatterIban.addSpecification(new IbanSpecification("QA", 29, "U04A21", "QA30AAAA123456789012345678901"));
  formatterIban.addSpecification(new IbanSpecification("RO", 24, "U04A16", "RO49AAAA1B31007593840000"));
  formatterIban.addSpecification(new IbanSpecification("RS", 22, "F03F13F02", "RS35260005601001611379"));
  formatterIban.addSpecification(new IbanSpecification("SA", 24, "F02A18", "SA0380000000608010167519"));
  formatterIban.addSpecification(new IbanSpecification("SC", 31, "U04F04F16U03", "SC18SSCB11010000000000001497USD"));
  formatterIban.addSpecification(new IbanSpecification("SE", 24, "F03F16F01", "SE4550000000058398257466"));
  formatterIban.addSpecification(new IbanSpecification("SI", 19, "F05F08F02", "SI56263300012039086"));
  formatterIban.addSpecification(new IbanSpecification("SK", 24, "F04F06F10", "SK3112000000198742637541"));
  formatterIban.addSpecification(new IbanSpecification("SM", 27, "U01F05F05A12", "SM86U0322509800000000270100"));
  formatterIban.addSpecification(new IbanSpecification("ST", 25, "F08F11F02", "ST68000100010051845310112"));
  formatterIban.addSpecification(new IbanSpecification("SV", 28, "U04F20", "SV62CENR00000000000000700025"));
  formatterIban.addSpecification(new IbanSpecification("TL", 23, "F03F14F02", "TL380080012345678910157"));
  formatterIban.addSpecification(new IbanSpecification("TN", 24, "F02F03F13F02", "TN5910006035183598478831"));
  formatterIban.addSpecification(new IbanSpecification("TR", 26, "F05F01A16", "TR330006100519786457841326"));
  formatterIban.addSpecification(new IbanSpecification("UA", 29, "F25", "UA511234567890123456789012345"));
  formatterIban.addSpecification(new IbanSpecification("VA", 22, "F18", "VA59001123000012345678"));
  formatterIban.addSpecification(new IbanSpecification("VG", 24, "U04F16", "VG96VPVG0000012345678901"));
  formatterIban.addSpecification(new IbanSpecification("XK", 20, "F04F10F02", "XK051212012345678906"));
  formatterIban.addSpecification(new IbanSpecification("AO", 25, "F21", "AO69123456789012345678901"));
  formatterIban.addSpecification(new IbanSpecification("BF", 27, "F23", "BF2312345678901234567890123"));
  formatterIban.addSpecification(new IbanSpecification("BI", 16, "F12", "BI41123456789012"));
  formatterIban.addSpecification(new IbanSpecification("BJ", 28, "F24", "BJ39123456789012345678901234"));
  formatterIban.addSpecification(new IbanSpecification("CI", 28, "U02F22", "CI70CI1234567890123456789012"));
  formatterIban.addSpecification(new IbanSpecification("CM", 27, "F23", "CM9012345678901234567890123"));
  formatterIban.addSpecification(new IbanSpecification("CV", 25, "F21", "CV30123456789012345678901"));
  formatterIban.addSpecification(new IbanSpecification("DZ", 24, "F20", "DZ8612345678901234567890"));
  formatterIban.addSpecification(new IbanSpecification("IR", 26, "F22", "IR861234568790123456789012"));
  formatterIban.addSpecification(new IbanSpecification("MG", 27, "F23", "MG1812345678901234567890123"));
  formatterIban.addSpecification(new IbanSpecification("ML", 28, "U01F23", "ML15A12345678901234567890123"));
  formatterIban.addSpecification(new IbanSpecification("MZ", 25, "F21", "MZ25123456789012345678901"));
  formatterIban.addSpecification(new IbanSpecification("SN", 28, "U01F23", "SN52A12345678901234567890123"));
  formatterIban.addSpecification(new IbanSpecification("GF", 27, "F05F05A11F02", "GF121234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("GP", 27, "F05F05A11F02", "GP791234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("MQ", 27, "F05F05A11F02", "MQ221234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("RE", 27, "F05F05A11F02", "RE131234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("PF", 27, "F05F05A11F02", "PF281234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("TF", 27, "F05F05A11F02", "TF891234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("YT", 27, "F05F05A11F02", "YT021234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("NC", 27, "F05F05A11F02", "NC551234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("BL", 27, "F05F05A11F02", "BL391234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("MF", 27, "F05F05A11F02", "MF551234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("PM", 27, "F05F05A11F02", "PM071234512345123456789AB13"));
  formatterIban.addSpecification(new IbanSpecification("WF", 27, "F05F05A11F02", "WF621234512345123456789AB13"));
  return {
    formatterNumber,
    formatterIban
  };
};

class AuthHookManager {
  #b24HookParams;
  constructor(b24HookParams) {
    this.#b24HookParams = Object.freeze(
      Object.assign(
        {},
        b24HookParams
      )
    );
  }
  /**
   * @see Http.#prepareParams
   */
  getAuthData() {
    const domain = this.#b24HookParams.b24Url.replaceAll("https://", "").replaceAll("http://", "").replace(/:(80|443)$/, "");
    return {
      access_token: this.#b24HookParams.secret,
      refresh_token: "hook",
      expires_in: 0,
      domain,
      member_id: domain
    };
  }
  refreshAuth() {
    return Promise.resolve(this.getAuthData());
  }
  getUniq(prefix) {
    const authData = this.getAuthData();
    if (authData === false) {
      throw new Error("AuthData not init");
    }
    return [
      prefix,
      authData.member_id
    ].join("_");
  }
  /**
   * Get the account address BX24 ( https://name.bitrix24.com )
   */
  getTargetOrigin() {
    return `${this.#b24HookParams.b24Url}`;
  }
  /**
   * Get the account address BX24 with Path ( https://name.bitrix24.com/rest/1/xxxxx )
   */
  getTargetOriginWithPath() {
    return `${this.#b24HookParams.b24Url}/rest/${this.#b24HookParams.userId}/${this.#b24HookParams.secret}`;
  }
  /**
   * We believe that hooks are created only by the admin
   */
  get isAdmin() {
    return true;
  }
}

class B24Hook extends AbstractB24 {
  #authHookManager;
  // region Init ////
  constructor(b24HookParams) {
    super();
    this.#authHookManager = new AuthHookManager(
      b24HookParams
    );
    this._http = new Http(
      this.#authHookManager.getTargetOriginWithPath(),
      this.#authHookManager,
      this._getHttpOptions()
    );
    this._isInit = true;
  }
  setLogger(logger) {
    super.setLogger(logger);
  }
  // endregion ////
  get auth() {
    return this.#authHookManager;
  }
  // region Core ////
  // endregion ////
  // region Get ////
  /**
   * Get the account address BX24 ( https://name.bitrix24.com )
   */
  getTargetOrigin() {
    this._ensureInitialized();
    return this.#authHookManager.getTargetOrigin();
  }
  /**
   * Get the account address BX24 with Path ( https://name.bitrix24.com/rest/1/xxxxx )
   */
  getTargetOriginWithPath() {
    this._ensureInitialized();
    return this.#authHookManager.getTargetOriginWithPath();
  }
  // endregion ////
  // region Tools ////
  // endregion ////
}

var MessageCommands = /* @__PURE__ */ ((MessageCommands2) => {
  MessageCommands2["getInitData"] = "getInitData";
  MessageCommands2["setInstallFinish"] = "setInstallFinish";
  MessageCommands2["setInstall"] = "setInstall";
  MessageCommands2["refreshAuth"] = "refreshAuth";
  MessageCommands2["setAppOption"] = "setAppOption";
  MessageCommands2["setUserOption"] = "setUserOption";
  MessageCommands2["resizeWindow"] = "resizeWindow";
  MessageCommands2["reloadWindow"] = "reloadWindow";
  MessageCommands2["setTitle"] = "setTitle";
  MessageCommands2["setScroll"] = "setScroll";
  MessageCommands2["openApplication"] = "openApplication";
  MessageCommands2["closeApplication"] = "closeApplication";
  MessageCommands2["openPath"] = "openPath";
  MessageCommands2["imCallTo"] = "imCallTo";
  MessageCommands2["imPhoneTo"] = "imPhoneTo";
  MessageCommands2["imOpenMessenger"] = "imOpenMessenger";
  MessageCommands2["imOpenHistory"] = "imOpenHistory";
  MessageCommands2["selectUser"] = "selectUser";
  MessageCommands2["selectAccess"] = "selectAccess";
  MessageCommands2["selectCRM"] = "selectCRM";
  MessageCommands2["showAppForm"] = "showAppForm";
  return MessageCommands2;
})(MessageCommands || {});

class MessageManager {
  #appFrame;
  #callbackPromises;
  _logger = null;
  runCallbackHandler;
  constructor(appFrame) {
    this.#appFrame = appFrame;
    this.#callbackPromises = /* @__PURE__ */ new Map();
    this.runCallbackHandler = this._runCallback.bind(this);
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  // region Events ////
  /**
   * Subscribe to the onMessage event of the parent window
   */
  subscribe() {
    window.addEventListener(
      "message",
      this.runCallbackHandler
    );
  }
  /**
   * Unsubscribe from the onMessage event of the parent window
   */
  unsubscribe() {
    window.removeEventListener(
      "message",
      this.runCallbackHandler
    );
  }
  // endregion ////
  /**
   * Send message to parent window
   * The answer (if) we will get in _runCallback
   *
   * @param command
   * @param params
   */
  async send(command, params = null) {
    return new Promise((resolve, reject) => {
      let cmd;
      const promiseHandler = { resolve, reject, timeoutId: null };
      const keyPromise = this.#setCallbackPromise(promiseHandler);
      if (command.toString().indexOf(":") >= 0) {
        cmd = {
          method: command.toString(),
          params: !!params ? params : "",
          callback: keyPromise,
          appSid: this.#appFrame.getAppSid()
        };
      } else {
        cmd = command.toString();
        let listParams = [
          !!params ? JSON.stringify(params) : null,
          keyPromise,
          this.#appFrame.getAppSid()
        ];
        cmd += ":" + listParams.filter(Boolean).join(":");
      }
      this.getLogger().log(`send to ${this.#appFrame.getTargetOrigin()}`, { cmd });
      parent.postMessage(
        cmd,
        this.#appFrame.getTargetOrigin()
      );
      if (params?.isSafely) {
        this.#callbackPromises.get(keyPromise).timeoutId = window.setTimeout(
          () => {
            if (this.#callbackPromises.has(keyPromise)) {
              this.getLogger().warn(
                `Action ${command.toString()} stop by timeout`
              );
              this.#callbackPromises.delete(keyPromise);
              resolve({ isSafely: true });
            }
          },
          parseInt(String(params?.safelyTime || 900))
        );
      }
    });
  }
  /**
   * Fulfilling a promise based on messages from the parent window
   *
   * @param event
   * @private
   */
  _runCallback(event) {
    if (event.origin !== this.#appFrame.getTargetOrigin()) {
      return;
    }
    if (!!event.data) {
      this.getLogger().log(`get from ${event.origin}`, {
        data: event.data
      });
      let tmp = event.data.split(":");
      const cmd = {
        id: tmp[0],
        args: tmp.slice(1).join(":")
      };
      if (!!cmd.args) {
        cmd.args = JSON.parse(cmd.args);
      }
      if (this.#callbackPromises.has(cmd.id)) {
        const promise = this.#callbackPromises.get(cmd.id);
        if (!!promise.timeoutId) {
          clearTimeout(promise.timeoutId);
        }
        this.#callbackPromises.delete(cmd.id);
        promise.resolve(cmd.args);
      }
    }
  }
  /**
   * Storing a promise for a message from the parent window
   *
   * @param promiseHandler
   * @private
   *
   * @memo We don't use Symbol here, because we need to pass it to the parent and then find and restore it.
   */
  #setCallbackPromise(promiseHandler) {
    let key = Text.getUniqId();
    this.#callbackPromises.set(
      key,
      promiseHandler
    );
    return key;
  }
}

class AppFrame {
  #domain = "";
  #protocol = true;
  #appSid = null;
  #path = null;
  #lang = null;
  constructor(queryParams) {
    if (queryParams.DOMAIN) {
      this.#domain = queryParams.DOMAIN;
      this.#domain = this.#domain.replace(/:(80|443)$/, "");
    }
    this.#protocol = queryParams.PROTOCOL === true;
    if (queryParams.LANG) {
      this.#lang = queryParams.LANG;
    }
    if (queryParams.APP_SID) {
      this.#appSid = queryParams.APP_SID;
    }
  }
  /**
   * Initializes the data received from the parent window message.
   * @param data
   */
  initData(data) {
    if (!this.#domain) {
      this.#domain = data.DOMAIN;
    }
    if (!this.#path) {
      this.#path = data.PATH;
    }
    if (!this.#lang) {
      this.#lang = data.LANG;
    }
    this.#protocol = parseInt(data.PROTOCOL) === 1;
    this.#domain = this.#domain.replace(/:(80|443)$/, "");
    return this;
  }
  /**
   * Returns the sid of the application relative to the parent window like this `9c33468728e1d2c8c97562475edfd96`
   */
  getAppSid() {
    if (null === this.#appSid) {
      throw new Error(`Not init appSid`);
    }
    return this.#appSid;
  }
  /**
   * Get the account address BX24 ( https://name.bitrix24.com )
   */
  getTargetOrigin() {
    return `${this.#protocol ? "https" : "http"}://${this.#domain}`;
  }
  /**
   * Get the account address BX24 with Path ( https://name.bitrix24.com/rest )
   */
  getTargetOriginWithPath() {
    return this.getTargetOrigin() + (this.#path ?? "");
  }
  /**
   * Returns the localization of the B24 interface
   * @return {B24LangList} - default B24LangList.en
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-get-lang.html
   */
  getLang() {
    return this.#lang || B24LangList.en;
  }
}

class AuthManager {
  #accessToken = null;
  #refreshId = null;
  #authExpires = 0;
  #memberId = null;
  #isAdmin = false;
  #appFrame;
  #messageManager;
  constructor(appFrame, messageManager) {
    this.#appFrame = appFrame;
    this.#messageManager = messageManager;
  }
  /**
   * Initializes the data received from the parent window message.
   * @param data
   */
  initData(data) {
    if (!!data.AUTH_ID) {
      this.#accessToken = data.AUTH_ID;
      this.#refreshId = data.REFRESH_ID;
      this.#authExpires = (/* @__PURE__ */ new Date()).valueOf() + parseInt(data.AUTH_EXPIRES) * 1e3;
      this.#isAdmin = data.IS_ADMIN;
      this.#memberId = data.MEMBER_ID || "";
    }
    return this;
  }
  /**
   * Returns authorization data
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/system-functions/bx24-get-auth.html
   */
  getAuthData() {
    return this.#authExpires > (/* @__PURE__ */ new Date()).valueOf() ? {
      access_token: this.#accessToken,
      refresh_token: this.#refreshId,
      expires_in: this.#authExpires,
      domain: this.#appFrame.getTargetOrigin(),
      member_id: this.#memberId
    } : false;
  }
  /**
   * Updates authorization data through the parent window
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/system-functions/bx24-refresh-auth.html
   */
  async refreshAuth() {
    return this.#messageManager.send(
      MessageCommands.refreshAuth,
      {}
    ).then((data) => {
      this.#accessToken = data.AUTH_ID;
      this.#refreshId = data.REFRESH_ID;
      this.#authExpires = (/* @__PURE__ */ new Date()).valueOf() + parseInt(data.AUTH_EXPIRES) * 1e3;
      return Promise.resolve(
        this.getAuthData()
      );
    });
  }
  getUniq(prefix) {
    return [
      prefix,
      this.#memberId || ""
    ].join("_");
  }
  /**
   * Determines whether the current user has administrator rights
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-is-admin.html
   */
  get isAdmin() {
    return this.#isAdmin;
  }
}

class ParentManager {
  #messageManager;
  constructor(messageManager) {
    this.#messageManager = messageManager;
  }
  /**
   * The method closes the open modal window with the application
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-close-application.html
   */
  async closeApplication() {
    return this.#messageManager.send(
      MessageCommands.closeApplication,
      {
        /**
         * @memo There is no point - everything will be closed and timeout will not be able to do anything
         */
        isSafely: false
      }
    );
  }
  /**
   * Sets the size of the frame containing the application to the size of the frame's content.
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-fit-window.html
   *
   * @memo in certain situations it may not be executed (placement of the main window after installing the application), in this case isSafely mode will work
   */
  fitWindow() {
    let width = "100%";
    let height = this.getScrollSize().scrollHeight;
    return this.#messageManager.send(
      MessageCommands.resizeWindow,
      {
        width,
        height,
        isSafely: true
      }
    );
  }
  /**
   * Sets the size of the frame containing the application to the size of the frame's content.
   *
   * @param {number} width
   * @param {number} height
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-resize-window.html
   *
   * @memo in certain situations it may not be executed, in this case isSafely mode will be triggered
   */
  resizeWindow(width, height) {
    if (width > 0 && height > 0) {
      return this.#messageManager.send(
        MessageCommands.resizeWindow,
        {
          width,
          height,
          isSafely: true
        }
      );
    }
    return Promise.reject(new Error(
      `Wrong width:number = ${width} or height:number = ${height}`
    ));
  }
  /**
   * Automatically resize `document.body` of frame with application according to frame content dimensions
   * If you pass appNode, the height will be calculated relative to it
   *
   * @param {HTMLElement|null} appNode
   * @param {number} minHeight
   * @param {number} minWidth
   *
   * @return {Promise<void>}
   */
  async resizeWindowAuto(appNode = null, minHeight = 0, minWidth = 0) {
    const body = document.body;
    let width = Math.max(
      body.scrollWidth,
      body.offsetWidth
      //html.clientWidth,
      //html.scrollWidth,
      //html.offsetWidth
    );
    if (minWidth > 0) {
      width = Math.max(minWidth, width);
    }
    let height = Math.max(
      body.scrollHeight,
      body.offsetHeight
      //html.clientHeight,
      //html.scrollHeight,
      //html.offsetHeight
    );
    if (!!appNode) {
      height = Math.max(
        appNode.scrollHeight,
        appNode.offsetHeight
      );
    }
    if (minHeight > 0) {
      height = Math.max(minHeight, height);
    }
    return this.resizeWindow(
      width,
      height
    );
  }
  /**
   * This function returns the inner dimensions of the application frame
   *
   * @return {Promise<{scrollWidth: number; scrollHeight: number}>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-get-scroll-size.html
   */
  getScrollSize() {
    return useScrollSize();
  }
  /**
   * Scrolls the parent window
   *
   * @param {number} scroll should specify the vertical scrollbar position (0 - scroll to the very top)
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-scroll-parent-window.html
   */
  async scrollParentWindow(scroll) {
    if (!Number.isInteger(scroll)) {
      return Promise.reject(new Error("Wrong scroll number"));
    }
    if (scroll < 0) {
      scroll = 0;
    }
    return this.#messageManager.send(
      MessageCommands.setScroll,
      {
        scroll,
        isSafely: true
      }
    );
  }
  /**
   * Reload the page with the application (the whole page, not just the frame).
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-reload-window.html
   */
  async reloadWindow() {
    return this.#messageManager.send(
      MessageCommands.reloadWindow,
      {
        isSafely: true
      }
    );
  }
  /**
   * Set Page Title
   *
   * @param {string} title
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-set-title.html
   */
  async setTitle(title) {
    return this.#messageManager.send(
      MessageCommands.setTitle,
      {
        title: title.toString(),
        isSafely: true
      }
    );
  }
  /**
   * Initiates a call via internal communication
   *
   * @param {number} userId The identifier of the account user
   * @param {boolean} isVideo true - video call, false - audio call. Optional parameter.
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-im-call-to.html
   */
  async imCallTo(userId, isVideo = true) {
    return this.#messageManager.send(
      MessageCommands.imCallTo,
      {
        userId,
        video: isVideo,
        isSafely: true
      }
    );
  }
  /**
   * Makes a call to the phone number
   *
   * @param {string} phone Phone number. The number can be in the format: `+44 20 1234 5678` or `x (xxx) xxx-xx-xx`
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-im-phone-to.html
   */
  async imPhoneTo(phone) {
    return this.#messageManager.send(
      MessageCommands.imPhoneTo,
      {
        phone,
        isSafely: true
      }
    );
  }
  /**
   * Opens the messenger window
   * userId or chatXXX - chat, where XXX is the chat identifier, which can simply be a number.
   * sgXXX - group chat, where XXX is the social network group number (the chat must be enabled in this group).
   *
   * XXXX** - open line, where XXX is the code obtained via the Rest method imopenlines.network.join.
   *
   * If nothing is passed, the chat interface will open with the last opened dialog.
   *
   * @param {number|`chat${number}`|`sg${number}`|`imol|${number}`|undefined} dialogId
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-im-open-messenger.html
   * @link https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=93&LESSON_ID=20152&LESSON_PATH=7657.7883.8025.20150.20152
   *
   */
  async imOpenMessenger(dialogId) {
    return this.#messageManager.send(
      MessageCommands.imOpenMessenger,
      {
        dialogId,
        isSafely: true
      }
    );
  }
  /**
   * Opens the history window
   * Identifier of the dialog:
   *
   * userId or chatXXX - chat, where XXX is the chat identifier, which can simply be a number.
   * imol|XXXX - open line, where XXX is the session number of the open line.
   *
   * @param {number|`chat${number}`|`imol|${number}`} dialogId
   *
   * @return {Promise<void>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-im-open-history.html
   */
  async imOpenHistory(dialogId) {
    return this.#messageManager.send(
      MessageCommands.imOpenHistory,
      {
        dialogId,
        isSafely: true
      }
    );
  }
}

let OptionsManager$1 = class OptionsManager {
  #messageManager;
  #appOptions = null;
  #userOptions = null;
  constructor(messageManager) {
    this.#messageManager = messageManager;
  }
  /**
   * Initializes the data received from the parent window message.
   * @param data
   */
  initData(data) {
    if (!!data.APP_OPTIONS) {
      this.#appOptions = data.APP_OPTIONS;
    }
    if (!!data.USER_OPTIONS) {
      this.#userOptions = data.USER_OPTIONS;
    }
    return this;
  }
  /**
   * Getting application option
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/options/bx24-app-option-get.html
   */
  appGet(option) {
    if (this.#appOptions && !!this.#appOptions[option]) {
      return this.#appOptions[option];
    }
    throw new Error(`app.option.${option} not set`);
  }
  /**
   * Updates application data through the parent window
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/options/bx24-app-option-set.html
   */
  async appSet(option, value) {
    if (!this.#appOptions) {
      this.#appOptions = [];
    }
    this.#appOptions[option] = value;
    return this.#sendParentMessage(
      MessageCommands.setAppOption,
      option,
      this.#appOptions[option]
    );
  }
  /**
   * Getting user option
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/options/bx24-user-option-get.html
   */
  userGet(option) {
    if (this.#userOptions && !!this.#userOptions[option]) {
      return this.#userOptions[option];
    }
    throw new Error(`user.option.${option} not set`);
  }
  /**
   * Updates user data through the parent window
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/options/bx24-user-option-set.html
   */
  async userSet(option, value) {
    if (!this.#appOptions) {
      this.#appOptions = [];
    }
    if (!this.#appOptions[option]) {
      this.#appOptions[option] = null;
    }
    this.#userOptions[option] = value;
    return this.#sendParentMessage(
      MessageCommands.setUserOption,
      option,
      // @ts-ignore
      this.#userOptions[option]
    );
  }
  async #sendParentMessage(command, option, value) {
    return this.#messageManager.send(
      command,
      {
        name: option,
        value,
        isSafely: true
      }
    ).then(() => {
      return Promise.resolve();
    });
  }
};

class DialogManager {
  #messageManager;
  constructor(messageManager) {
    this.#messageManager = messageManager;
  }
  /**
   * Method displays the standard single user selection dialog
   * It only shows company employees
   *
   * @return {Promise<null|SelectedUser>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/system-dialogues/bx24-select-user.html
   */
  async selectUser() {
    return this.#messageManager.send(
      MessageCommands.selectUser,
      {
        mult: false
      }
    );
  }
  /**
   * Method displays the standard multiple user selection dialog
   * It only shows company employees
   *
   * @return {Promise<SelectedUser[]>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/system-dialogues/bx24-select-users.html
   */
  async selectUsers() {
    return this.#messageManager.send(
      MessageCommands.selectUser,
      {
        mult: true
      }
    );
  }
  /**
   * @deprecated
   * Method displays a standard access permission selection dialog
   *
   * @param {string[]} blockedAccessPermissions
   * @return {Promise<SelectedAccess[]>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/system-dialogues/bx24-select-access.html
   */
  async selectAccess(blockedAccessPermissions = []) {
    console.warn(`@deprecated selectAccess`);
    return this.#messageManager.send(
      MessageCommands.selectAccess,
      {
        value: blockedAccessPermissions
      }
    );
  }
  /**
   * @deprecated
   * Method invokes the system dialog for selecting a CRM entity
   *
   * @param {SelectCRMParams} params
   * @return {Promise<SelectedCRM>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/system-dialogues/bx24-select-crm.html
   */
  async selectCRM(params) {
    console.warn(`@deprecated selectCRM`);
    return this.#messageManager.send(
      MessageCommands.selectCRM,
      {
        entityType: params?.entityType,
        multiple: params?.multiple,
        value: params?.value
      }
    );
  }
}

class SliderManager {
  #appFrame;
  #messageManager;
  constructor(appFrame, messageManager) {
    this.#appFrame = appFrame;
    this.#messageManager = messageManager;
  }
  /**
   * Returns the URL relative to the domain name and path
   */
  getUrl(path = "/") {
    return new URL(path, this.#appFrame.getTargetOrigin());
  }
  /**
   * Get the account address BX24
   */
  getTargetOrigin() {
    return this.#appFrame.getTargetOrigin();
  }
  /**
   * When the method is called, a pop-up window with the application frame will be opened.
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-open-application.html
   */
  async openSliderAppPage(params = {}) {
    return this.#messageManager.send(
      MessageCommands.openApplication,
      params
    );
  }
  /**
   * Defines the base path for width sampling.
   *
   * @param width
   * @private
   */
  #getBaseUrlByWidth(width = 1640) {
    if (width > 0) {
      if (width > 1200 && width <= 1640) {
        return "/crm/type/0/details/0/../../../../..";
      } else if (width > 950 && width <= 1200) {
        return "/company/personal/user/0/groups/create/../../../../../..";
      } else if (width > 900 && width <= 950) {
        return "/crm/company/requisite/0/../../../..";
      } else if (width <= 900) {
        return "/workgroups/group/0/card/../../../..";
      } else {
        return "/crm/deal/../..";
      }
    } else {
      return "/crm/deal/../..";
    }
  }
  /**
   * Opens the specified path inside the portal in the slider.
   * @param {URL} url
   * @param {number} width - Number in the range from 1640 to 1200, from 1200 to 950, from 950 to 900, from 900 ...
   * @return {Promise<StatusClose>}
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-open-path.html
   * @memo /^\/(crm\/(deal|lead|contact|company|type)|marketplace|company\/personal\/user\/[0-9]+|workgroups\/group\/[0-9]+)\//
   */
  async openPath(url, width = 1640) {
    const openSliderUrl = new URL(url);
    openSliderUrl.searchParams.set("IFRAME", "Y");
    openSliderUrl.searchParams.set("IFRAME_TYPE", "SIDE_SLIDER");
    return this.#messageManager.send(
      MessageCommands.openPath,
      {
        path: [this.#getBaseUrlByWidth(width), openSliderUrl.pathname, openSliderUrl.search].join("")
      }
    ).then((response) => {
      if (response?.result === "error") {
        if (response?.errorCode === "METHOD_NOT_SUPPORTED_ON_DEVICE") {
          return new Promise((resolve, reject) => {
            const windowObjectReference = window.open(url, "_blank");
            if (!windowObjectReference) {
              reject(new Error("Error open window"));
              return;
            }
            let iterator = 0;
            let iteratorMax = 1e3 * 60 * 5;
            let waitCloseWindow = window.setInterval(() => {
              iterator = iterator + 1;
              if (windowObjectReference.closed) {
                clearInterval(waitCloseWindow);
                resolve({
                  isOpenAtNewWindow: true,
                  isClose: true
                });
              } else if (iterator > iteratorMax) {
                clearInterval(waitCloseWindow);
                resolve({
                  isOpenAtNewWindow: true,
                  isClose: false
                });
              }
            }, 1e3);
          });
        } else {
          return Promise.reject(new Error(
            response?.errorCode
          ));
        }
      } else if (response?.result === "close") {
        return Promise.resolve({
          isOpenAtNewWindow: false,
          isClose: true
        });
      }
      return Promise.resolve({
        isOpenAtNewWindow: false,
        isClose: false
      });
    });
  }
  /**
   * @deprecated
   * @param params
   */
  async showAppForm(params) {
    console.warn(`@deprecated showAppForm`);
    return this.#messageManager.send(
      MessageCommands.showAppForm,
      {
        params,
        isSafely: true
      }
    );
  }
}

class PlacementManager {
  #title = "";
  #options = {};
  constructor() {
  }
  /**
   * Initializes the data received from the parent window message.
   * @param data
   */
  initData(data) {
    this.#title = data.PLACEMENT || "DEFAULT";
    const options = data.PLACEMENT_OPTIONS;
    this.#options = Object.freeze(options);
    return this;
  }
  get title() {
    return this.#title;
  }
  get isDefault() {
    return this.title === "DEFAULT";
  }
  get options() {
    return this.#options;
  }
  get isSliderMode() {
    return this.options?.IFRAME === "Y";
  }
}

class B24Frame extends AbstractB24 {
  #isInstallMode = false;
  #isFirstRun = false;
  #appFrame;
  #messageManager;
  #authManager;
  #parentManager;
  #optionsManager;
  #dialogManager;
  #sliderManager;
  #placementManager;
  // region Init ////
  constructor(queryParams) {
    super();
    this.#appFrame = new AppFrame(queryParams);
    this.#messageManager = new MessageManager(this.#appFrame);
    this.#messageManager.subscribe();
    this.#authManager = new AuthManager(this.#appFrame, this.#messageManager);
    this.#parentManager = new ParentManager(this.#messageManager);
    this.#optionsManager = new OptionsManager$1(this.#messageManager);
    this.#dialogManager = new DialogManager(this.#messageManager);
    this.#sliderManager = new SliderManager(this.#appFrame, this.#messageManager);
    this.#placementManager = new PlacementManager();
    this._isInit = false;
  }
  setLogger(logger) {
    super.setLogger(logger);
    this.#messageManager.setLogger(this.getLogger());
  }
  get isFirstRun() {
    this._ensureInitialized();
    return this.#isFirstRun;
  }
  get isInstallMode() {
    this._ensureInitialized();
    return this.#isInstallMode;
  }
  get parent() {
    this._ensureInitialized();
    return this.#parentManager;
  }
  get auth() {
    this._ensureInitialized();
    return this.#authManager;
  }
  get slider() {
    this._ensureInitialized();
    return this.#sliderManager;
  }
  get placement() {
    this._ensureInitialized();
    return this.#placementManager;
  }
  get options() {
    this._ensureInitialized();
    return this.#optionsManager;
  }
  get dialog() {
    this._ensureInitialized();
    return this.#dialogManager;
  }
  async init() {
    return this.#messageManager.send(
      MessageCommands.getInitData,
      {}
    ).then((data) => {
      this.getLogger().log("init data:", data);
      this.#appFrame.initData(data);
      this.#authManager.initData(data);
      this.#placementManager.initData(data);
      this.#optionsManager.initData(data);
      this.#isInstallMode = data.INSTALL;
      this.#isFirstRun = data.FIRST_RUN;
      this._http = new Http(
        this.#appFrame.getTargetOriginWithPath(),
        this.#authManager,
        this._getHttpOptions()
      );
      if (this.#isFirstRun) {
        return this.#messageManager.send(
          MessageCommands.setInstall,
          {
            install: true
          }
        );
      }
      this._isInit = true;
      return Promise.resolve();
    });
  }
  /**
   * Destructor.
   * Removes an event subscription
   */
  destroy() {
    this.#messageManager.unsubscribe();
    super.destroy();
  }
  // endregion ////
  // region Core ////
  /**
   * Signals that the installer or application setup has finished running.
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/system-functions/bx24-install-finish.html
   */
  async installFinish() {
    if (!this.isInstallMode) {
      return Promise.reject(new Error("Application was previously installed. You cannot call installFinish"));
    }
    return this.#messageManager.send(
      MessageCommands.setInstallFinish,
      {}
    );
  }
  // endregion ////
  // region Get ////
  /**
   * Get the account address BX24 ( https://name.bitrix24.com )
   */
  getTargetOrigin() {
    this._ensureInitialized();
    return this.#appFrame.getTargetOrigin();
  }
  /**
   * Get the account address BX24 with Path ( https://name.bitrix24.com/rest )
   */
  getTargetOriginWithPath() {
    this._ensureInitialized();
    return this.#appFrame.getTargetOriginWithPath();
  }
  /**
   * Returns the sid of the application relative to the parent window like this `9c33468728e1d2c8c97562475edfd96`
   */
  getAppSid() {
    this._ensureInitialized();
    return this.#appFrame.getAppSid();
  }
  /**
   * Returns the localization of the B24 interface
   *
   * @link https://apidocs.bitrix24.com/api-reference/bx24-js-sdk/additional-functions/bx24-get-lang.html
   */
  getLang() {
    this._ensureInitialized();
    return this.#appFrame.getLang();
  }
  // endregion ////
}

class UnhandledMatchError extends Error {
  constructor(value, ...args) {
    super(...args);
    this.name = "UnhandledMatchError";
    this.message = `Unhandled match value of type ${value}`;
    this.stack = new Error().stack;
  }
}
class AbstractHelper {
  _b24;
  _logger = null;
  _data = null;
  // region Init ////
  constructor(b24) {
    this._b24 = b24;
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  // endregion ////
  /**
   * Initializes the data received
   * @param data
   */
  async initData(data) {
    this.getLogger().log(data);
    return Promise.reject(new Error("Rewrite this function"));
  }
}

class ProfileManager extends AbstractHelper {
  _data = null;
  /**
   * @inheritDoc
   */
  async initData(data) {
    this._data = data;
  }
  get data() {
    if (null === this._data) {
      throw new Error("ProfileManager.data not initialized");
    }
    return this._data;
  }
}

class AppManager extends AbstractHelper {
  _data = null;
  /**
   * @inheritDoc
   */
  async initData(data) {
    this._data = data;
  }
  get data() {
    if (null === this._data) {
      throw new Error("AppManager.data not initialized");
    }
    return this._data;
  }
  get statusCode() {
    return StatusDescriptions[this.data.status] || "Unknown status";
  }
}

class PaymentManager extends AbstractHelper {
  _data = null;
  /**
   * @inheritDoc
   */
  async initData(data) {
    this._data = data;
  }
  get data() {
    if (null === this._data) {
      throw new Error("PaymentManager.data not initialized");
    }
    return this._data;
  }
}

class LicenseManager extends AbstractHelper {
  _data = null;
  /**
   * @inheritDoc
   */
  async initData(data) {
    this._data = data;
    this.makeRestrictionManagerParams();
  }
  get data() {
    if (null === this._data) {
      throw new Error("LicenseManager.data not initialized");
    }
    return this._data;
  }
  /**
   * Set RestrictionManager params by license
   * @link https://apidocs.bitrix24.com/api-reference/common/system/app-info.html
   */
  makeRestrictionManagerParams() {
    if (!this.data?.license) {
      return;
    }
    if (this.data.license.includes("ent")) {
      this.getLogger().log(
        `LICENSE ${this.data.license} => up restriction manager params`,
        RestrictionManagerParamsForEnterprise
      );
      this._b24.getHttpClient().setRestrictionManagerParams(
        RestrictionManagerParamsForEnterprise
      );
    }
  }
}

class CurrencyManager extends AbstractHelper {
  /**
   * @inheritDoc
   */
  async initData(data) {
    this._data = {
      currencyBase: "?",
      currencyList: /* @__PURE__ */ new Map()
    };
    this.setBaseCurrency(data.currencyBase);
    this.setCurrencyList(data.currencyList);
    try {
      await this.loadData();
    } catch (error) {
      console.error(error);
      throw new Error("Failed to load data");
    }
  }
  async loadData() {
    const batchRequest = this.currencyList.map((currencyCode) => {
      return {
        method: "crm.currency.get",
        params: {
          id: currencyCode
        }
      };
    });
    if (batchRequest.length < 1) {
      return Promise.resolve();
    }
    try {
      const response = await this._b24.callBatchByChunk(batchRequest, true);
      const data = response.getData();
      data.forEach((row) => {
        if (typeof row.LANG === "undefined") {
          return;
        }
        const currencyCode = row.CURRENCY;
        const currency = this.data.currencyList.get(currencyCode);
        if (typeof currency === "undefined") {
          return;
        }
        Object.entries(row.LANG).forEach(([langCode, formatData]) => {
          currency.lang[langCode] = {
            decimals: parseInt(formatData.DECIMALS),
            decPoint: formatData.DEC_POINT,
            formatString: formatData.FORMAT_STRING,
            fullName: formatData.FULL_NAME,
            isHideZero: formatData.HIDE_ZERO === "Y",
            thousandsSep: formatData.THOUSANDS_SEP,
            thousandsVariant: formatData.THOUSANDS_VARIANT
          };
          switch (currency.lang[langCode].thousandsVariant) {
            case "N":
              currency.lang[langCode].thousandsSep = "";
              break;
            case "D":
              currency.lang[langCode].thousandsSep = ".";
              break;
            case "C":
              currency.lang[langCode].thousandsSep = ",";
              break;
            case "S":
              currency.lang[langCode].thousandsSep = " ";
              break;
            case "B":
              currency.lang[langCode].thousandsSep = "&nbsp;";
              break;
            case "OWN":
            default:
              if (!Type.isStringFilled(currency.lang[langCode].thousandsSep)) {
                currency.lang[langCode].thousandsSep = " ";
              }
              break;
          }
        });
      });
    } catch (error) {
      console.error(error);
    }
  }
  get data() {
    if (null === this._data) {
      throw new Error("CurrencyManager.data not initialized");
    }
    return this._data;
  }
  // region BaseCurrency ////
  setBaseCurrency(currencyBase) {
    this._data.currencyBase = currencyBase;
  }
  get baseCurrency() {
    return this.data.currencyBase;
  }
  // endregion ////
  // region CurrencyList ////
  setCurrencyList(list = []) {
    this.data.currencyList.clear();
    list.forEach((row) => {
      this.data.currencyList.set(
        row.CURRENCY,
        {
          amount: parseFloat(row.CURRENCY),
          amountCnt: parseInt(row.AMOUNT_CNT),
          isBase: row.BASE === "Y",
          currencyCode: row.CURRENCY,
          dateUpdate: Text.toDateTime(row.DATE_UPDATE),
          decimals: parseInt(row.DECIMALS),
          decPoint: row.DEC_POINT,
          formatString: row.FORMAT_STRING,
          fullName: row.FULL_NAME,
          lid: row.LID,
          sort: parseInt(row.SORT),
          thousandsSep: row?.THOUSANDS_SEP || null,
          lang: {}
        }
      );
    });
  }
  // endregion ////
  // region Info ////
  getCurrencyFullName(currencyCode, langCode) {
    const currency = this.data.currencyList.get(currencyCode);
    if (typeof currency === "undefined") {
      throw new UnhandledMatchError(currencyCode);
    }
    let fullName = currency.fullName;
    if (!(typeof langCode === "undefined")) {
      const langFormatter = currency.lang[langCode];
      if (!Type.isUndefined(langFormatter)) {
        fullName = langFormatter.fullName;
      }
    }
    return fullName;
  }
  getCurrencyLiteral(currencyCode, langCode) {
    const currency = this.data.currencyList.get(currencyCode);
    if (typeof currency === "undefined") {
      throw new UnhandledMatchError(currencyCode);
    }
    let formatString = currency.formatString;
    if (!(typeof langCode === "undefined")) {
      const langFormatter = currency.lang[langCode];
      if (!Type.isUndefined(langFormatter)) {
        formatString = langFormatter.formatString;
      }
    }
    return formatString.replaceAll("&#", "&%").replaceAll("#", "").replaceAll("&%", "&#").trim() || "";
  }
  get currencyList() {
    return Array.from(this.data.currencyList.keys());
  }
  // endregion ////
  // region Format ////
  format(value, currencyCode, langCode) {
    const currency = this.data.currencyList.get(currencyCode);
    if (typeof currency === "undefined") {
      throw new UnhandledMatchError(currencyCode);
    }
    const options = {
      formatString: currency.formatString,
      decimals: currency.decimals,
      decPoint: currency.decPoint,
      thousandsSep: currency.thousandsSep
    };
    if (!Type.isStringFilled(options.thousandsSep)) {
      options.thousandsSep = "";
    }
    const langFormatter = currency.lang[langCode];
    if (!Type.isUndefined(langFormatter)) {
      options.formatString = langFormatter.formatString;
      options.decimals = langFormatter.decimals;
      options.decPoint = langFormatter.decPoint;
      options.thousandsSep = langFormatter.thousandsSep;
    }
    return options.formatString.replaceAll("&#", "&%").replace(
      "#",
      Text.numberFormat(
        value,
        options.decimals,
        options.decPoint,
        options.thousandsSep
      )
    ).replaceAll("&%", "&#") || "";
  }
  // endregion ////
}

class OptionsManager extends AbstractHelper {
  _data;
  _type;
  // region static ////
  static getSupportTypes() {
    return [
      TypeOption.NotSet,
      TypeOption.JsonArray,
      TypeOption.JsonObject,
      TypeOption.FloatVal,
      TypeOption.IntegerVal,
      TypeOption.BoolYN,
      TypeOption.StringVal
    ];
  }
  static prepareArrayList(list) {
    if (Type.isArray(list)) {
      return list;
    }
    if (Type.isObject(list)) {
      return Array.from(Object.values(list));
    }
    return [];
  }
  // endregion ////
  // region Init ////
  constructor(b24, type) {
    super(b24);
    this._type = type;
    this._data = /* @__PURE__ */ new Map();
  }
  get data() {
    return this._data;
  }
  reset() {
    this.data.clear();
  }
  /**
   * @inheritDoc
   */
  async initData(data) {
    this.reset();
    if (Type.isObject(data)) {
      Object.entries(data).forEach(([key, value]) => {
        this.data.set(
          key,
          value
        );
      });
    }
  }
  // endregion ////
  // region Get ////
  getJsonArray(key, defValue = []) {
    if (!this.data.has(key)) {
      return defValue;
    }
    let data = this.data.get(key);
    try {
      data = JSON.parse(data);
      if (!Type.isArray(data) && !Type.isObject(data)) {
        data = defValue;
      }
    } catch (error) {
      this.getLogger().error(error);
      data = defValue;
    }
    return OptionsManager.prepareArrayList(data);
  }
  getJsonObject(key, defValue = {}) {
    if (!this.data.has(key)) {
      return defValue;
    }
    let data = this.data.get(key);
    try {
      data = JSON.parse(data);
    } catch (error) {
      this.getLogger().error(error);
      data = defValue;
    }
    if (!Type.isObject(data)) {
      data = defValue;
    }
    return data;
  }
  getFloat(key, defValue = 0) {
    if (!this.data.has(key)) {
      return defValue;
    }
    return Text.toNumber(this.data.get(key));
  }
  getInteger(key, defValue = 0) {
    if (!this.data.has(key)) {
      return defValue;
    }
    return Text.toInteger(this.data.get(key));
  }
  getBoolYN(key, defValue = true) {
    if (!this.data.has(key)) {
      return defValue;
    }
    return Text.toBoolean(this.data.get(key));
  }
  getBoolNY(key, defValue = false) {
    if (!this.data.has(key)) {
      return defValue;
    }
    return Text.toBoolean(this.data.get(key));
  }
  getString(key, defValue = "") {
    if (!this.data.has(key)) {
      return defValue;
    }
    return this.data.get(key).toString();
  }
  getDate(key, defValue = null) {
    if (!this.data.has(key)) {
      return defValue;
    }
    try {
      let result = Text.toDateTime(
        this.data.get(key).toString()
      );
      if (result.isValid) {
        return result;
      } else {
        return defValue;
      }
    } catch (error) {
      return defValue;
    }
  }
  // endregion ////
  // region Tools ////
  encode(value) {
    return JSON.stringify(value);
  }
  decode(data, defaultValue) {
    try {
      if (data.length > 0) {
        return JSON.parse(data);
      }
      return defaultValue;
    } catch (error) {
      this.getLogger().warn(error, data);
    }
    return defaultValue;
  }
  // endregion ////
  // region Save ////
  getMethodSave() {
    switch (this._type) {
      case "app":
        return "app.option.set";
      case "user":
        return "user.option.set";
    }
  }
  async save(options, optionsPull) {
    const commands = [];
    commands.push({
      method: this.getMethodSave(),
      params: {
        options
      }
    });
    if (Type.isObject(optionsPull)) {
      commands.push({
        method: "pull.application.event.add",
        params: {
          COMMAND: optionsPull?.command,
          PARAMS: optionsPull?.params,
          MODULE_ID: optionsPull?.moduleId
        }
      });
    }
    return this._b24.callBatch(commands, true);
  }
  // endregion ////
}

class StorageManager {
  _logger = null;
  userId;
  siteId;
  constructor(params = {}) {
    this.userId = params.userId ? Text.toInteger(params.userId) : 0;
    this.siteId = params.siteId ? params.siteId : "none";
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  set(name, value) {
    if (typeof window.localStorage === "undefined") {
      this.getLogger().error(new Error("undefined window.localStorage"));
      return;
    }
    if (typeof value !== "string") {
      if (value) {
        value = JSON.stringify(value);
      }
    }
    window.localStorage.setItem(
      this._getKey(name),
      value
    );
  }
  get(name, defaultValue) {
    if (typeof window.localStorage === "undefined") {
      return defaultValue || null;
    }
    const result = window.localStorage.getItem(this._getKey(name));
    if (result === null) {
      return defaultValue || null;
    }
    return JSON.parse(result);
  }
  remove(name) {
    if (typeof window.localStorage === "undefined") {
      this.getLogger().error(new Error("undefined window.localStorage"));
      return;
    }
    return window.localStorage.removeItem(
      this._getKey(name)
    );
  }
  _getKey(name) {
    return `@bitrix24/b24jssdk-pull-${this.userId}-${this.siteId}-${name}`;
  }
  compareKey(eventKey, userKey) {
    return eventKey === this._getKey(userKey);
  }
}

class ErrorNotConnected extends Error {
  constructor(message) {
    super(message);
    this.name = "ErrorNotConnected";
  }
}
class ErrorTimeout extends Error {
  constructor(message) {
    super(message);
    this.name = "ErrorTimeout";
  }
}

const JSON_RPC_VERSION = "2.0";
class JsonRpc {
  _logger = null;
  _connector;
  _idCounter = 0;
  _handlers = {};
  _rpcResponseAwaiters = /* @__PURE__ */ new Map();
  constructor(options) {
    this._connector = options.connector;
    if (Type.isPlainObject(options.handlers)) {
      for (let method in options.handlers) {
        this.handle(method, options.handlers[method]);
      }
    }
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  /**
   * @param {string} method
   * @param {function} handler
   */
  handle(method, handler) {
    this._handlers[method] = handler;
  }
  /**
   * Sends RPC command to the server.
   *
   * @param {string} method Method name
   * @param {object} params
   * @param {int} timeout
   * @returns {Promise}
   */
  async executeOutgoingRpcCommand(method, params, timeout = 5) {
    return new Promise((resolve, reject) => {
      const request = this.createRequest(
        method,
        params
      );
      if (!this._connector.send(
        JSON.stringify(request)
      )) {
        reject(new ErrorNotConnected("websocket is not connected"));
      }
      const timeoutHandler = setTimeout(
        () => {
          this._rpcResponseAwaiters.delete(request.id);
          reject(new ErrorTimeout("no response"));
        },
        timeout * 1e3
      );
      this._rpcResponseAwaiters.set(
        request.id,
        {
          resolve,
          reject,
          timeout: timeoutHandler
        }
      );
    });
  }
  /**
   * Executes array or rpc commands.
   * Returns array of promises, each promise will be resolved individually.
   *
   * @param {JsonRpcRequest[]} batch
   * @returns {Promise[]}
   */
  executeOutgoingRpcBatch(batch) {
    let requests = [];
    let promises = [];
    batch.forEach(({ method, params, id }) => {
      const request = this.createRequest(
        method,
        params,
        id
      );
      requests.push(request);
      promises.push(new Promise((resolve, reject) => this._rpcResponseAwaiters.set(
        request.id,
        {
          resolve,
          reject
        }
      )));
    });
    this._connector.send(JSON.stringify(requests));
    return promises;
  }
  processRpcResponse(response) {
    if ("id" in response && this._rpcResponseAwaiters.has(Number(response.id))) {
      const awaiter = this._rpcResponseAwaiters.get(Number(response.id));
      if (awaiter) {
        if ("result" in response) {
          awaiter.resolve(response.result);
        } else if ("error" in response) {
          awaiter.reject(response?.error || "error");
        } else {
          awaiter.reject("wrong response structure");
        }
        clearTimeout(awaiter.timeout);
        this._rpcResponseAwaiters.delete(Number(response.id));
      }
      return;
    }
    this.getLogger().error(
      new Error(
        `${Text.getDateForLog()}: Pull: Received rpc response with unknown id`
      ),
      response
    );
  }
  parseJsonRpcMessage(message) {
    let decoded;
    try {
      decoded = JSON.parse(message);
    } catch (error) {
      this.getLogger().error(
        new Error(
          `${Text.getDateForLog()}: Pull: Could not decode json rpc message`
        ),
        error
      );
      return;
    }
    if (Type.isArray(decoded)) {
      return this.executeIncomingRpcBatch(decoded);
    } else if (Type.isJsonRpcRequest(decoded)) {
      return this.executeIncomingRpcCommand(decoded);
    } else if (Type.isJsonRpcResponse(decoded)) {
      return this.processRpcResponse(decoded);
    } else {
      this.getLogger().error(
        new Error(
          `${Text.getDateForLog()}: Pull: unknown rpc packet`
        ),
        decoded
      );
    }
  }
  /**
   * Executes RPC command, received from the server
   *
   * @param {string} method
   * @param {object} params
   * @returns {object}
   */
  executeIncomingRpcCommand({ method, params }) {
    if (method in this._handlers) {
      return this._handlers[method].call(this, params || {});
    }
    return {
      jsonrpc: JSON_RPC_VERSION,
      error: ListRpcError.MethodNotFound
    };
  }
  executeIncomingRpcBatch(batch) {
    let result = [];
    for (let command of batch) {
      if ("jsonrpc" in command) {
        if ("method" in command) {
          let commandResult = this.executeIncomingRpcCommand(command);
          if (commandResult) {
            commandResult["jsonrpc"] = JSON_RPC_VERSION;
            commandResult["id"] = command["id"];
            result.push(commandResult);
          }
        } else {
          this.processRpcResponse(command);
        }
      } else {
        this.getLogger().error(
          new Error(
            `${Text.getDateForLog()}: Pull: unknown rpc command in batch`
          ),
          command
        );
        result.push({
          jsonrpc: JSON_RPC_VERSION,
          error: ListRpcError.InvalidRequest
        });
      }
    }
    return result;
  }
  nextId() {
    return ++this._idCounter;
  }
  createPublishRequest(messageBatch) {
    return messageBatch.map(
      (message) => this.createRequest(
        "publish",
        message
      )
    );
  }
  createRequest(method, params, id) {
    if (!id) {
      id = this.nextId();
    }
    return {
      jsonrpc: JSON_RPC_VERSION,
      method,
      params,
      id
    };
  }
}

class SharedConfig {
  _logger = null;
  _storage;
  _ttl = 24 * 60 * 60;
  _callbacks;
  constructor(params = {}) {
    params = params || {};
    this._storage = params.storage || new StorageManager();
    this._callbacks = {
      onWebSocketBlockChanged: Type.isFunction(params.onWebSocketBlockChanged) ? params.onWebSocketBlockChanged : () => {
      }
    };
    if (this._storage) {
      window.addEventListener(
        "storage",
        this.onLocalStorageSet.bind(this)
      );
    }
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  onLocalStorageSet(params) {
    if (this._storage.compareKey(
      params.key || "",
      LsKeys.WebsocketBlocked
    ) && params.newValue !== params.oldValue) {
      this._callbacks.onWebSocketBlockChanged({
        isWebSocketBlocked: this.isWebSocketBlocked()
      });
    }
  }
  isWebSocketBlocked() {
    if (!this._storage) {
      return false;
    }
    return this._storage.get(LsKeys.WebsocketBlocked, 0) > (/* @__PURE__ */ new Date()).getTime();
  }
  setWebSocketBlocked(isWebSocketBlocked) {
    if (!this._storage) {
      return false;
    }
    try {
      this._storage.set(
        LsKeys.WebsocketBlocked,
        isWebSocketBlocked ? (/* @__PURE__ */ new Date()).getTime() + this._ttl : 0
      );
    } catch (error) {
      this.getLogger().error(new Error(
        `${Text.getDateForLog()}: Pull: Could not save WS_blocked flag in local storage. Error: `
      ), error);
      return false;
    }
    return true;
  }
  isLongPollingBlocked() {
    if (!this._storage) {
      return false;
    }
    return this._storage.get(LsKeys.LongPollingBlocked, 0) > (/* @__PURE__ */ new Date()).getTime();
  }
  setLongPollingBlocked(isLongPollingBlocked) {
    if (!this._storage) {
      return false;
    }
    try {
      this._storage.set(
        LsKeys.LongPollingBlocked,
        isLongPollingBlocked ? (/* @__PURE__ */ new Date()).getTime() + this._ttl : 0
      );
    } catch (error) {
      this.getLogger().error(
        new Error(
          `${Text.getDateForLog()}: Pull: Could not save LP_blocked flag in local storage. Error: `
        ),
        error
      );
      return false;
    }
    return true;
  }
  isLoggingEnabled() {
    if (!this._storage) {
      return false;
    }
    return this._storage.get(LsKeys.LoggingEnabled, 0) > this.getTimestamp();
  }
  setLoggingEnabled(isLoggingEnabled) {
    if (!this._storage) {
      return false;
    }
    try {
      this._storage.set(
        LsKeys.LoggingEnabled,
        isLoggingEnabled ? this.getTimestamp() + this._ttl : 0
      );
    } catch (error) {
      this.getLogger().error(new Error(
        `${Text.getDateForLog()}: LocalStorage error: `
      ), error);
      return false;
    }
    return true;
  }
  // region Tools ////
  getTimestamp() {
    return (/* @__PURE__ */ new Date()).getTime();
  }
  // endregion ////
}

class ChannelManager {
  _logger = null;
  _publicIds;
  _restClient;
  _getPublicListMethod;
  constructor(params) {
    this._publicIds = /* @__PURE__ */ new Map();
    this._restClient = params.b24;
    this._getPublicListMethod = params.getPublicListMethod;
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  /**
   * @param {Array} users Array of user ids.
   * @return {Promise}
   */
  async getPublicIds(users) {
    const now = /* @__PURE__ */ new Date();
    let result = {};
    let unknownUsers = [];
    users.forEach((userId) => {
      const chanel = this._publicIds.get(userId);
      if (chanel && chanel.end > now) {
        result[chanel.userId] = chanel;
      } else {
        unknownUsers.push(userId);
      }
    });
    if (unknownUsers.length === 0) {
      return Promise.resolve(result);
    }
    return new Promise((resolve) => {
      this._restClient.callMethod(
        this._getPublicListMethod,
        {
          users: unknownUsers
        }
      ).then((response) => {
        const data = response.getData().result;
        this.setPublicIds(Object.values(data));
        unknownUsers.forEach((userId) => {
          const chanel = this._publicIds.get(userId);
          if (chanel) {
            result[chanel.userId] = chanel;
          }
        });
        resolve(result);
      }).catch((error) => {
        this.getLogger().error(error);
        return resolve({});
      });
    });
  }
  /**
   * @param {TypePublicIdDescriptor[]} publicIds
   */
  setPublicIds(publicIds) {
    publicIds.forEach((publicIdDescriptor) => {
      const userId = Number(publicIdDescriptor.user_id);
      this._publicIds.set(
        userId,
        {
          userId,
          publicId: publicIdDescriptor.public_id,
          signature: publicIdDescriptor.signature,
          start: new Date(publicIdDescriptor.start),
          end: new Date(publicIdDescriptor.end)
        }
      );
    });
  }
}

let $Reader = $protobuf.Reader, $Writer = $protobuf.Writer, $util = $protobuf.util;
const $root = $protobuf.roots["push-server"] || ($protobuf.roots["push-server"] = {});
$root.RequestBatch = function() {
  function RequestBatch(properties) {
    this.requests = [];
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  RequestBatch.prototype.requests = $util.emptyArray;
  RequestBatch.create = function create(properties) {
    return new RequestBatch(properties);
  };
  RequestBatch.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.requests != null && message.requests.length)
      for (var i = 0; i < message.requests.length; ++i)
        $root.Request.encode(message.requests[i], writer.uint32(
          /* id 1, wireType 2 =*/
          10
        ).fork()).ldelim();
    return writer;
  };
  RequestBatch.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.RequestBatch();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          if (!(message.requests && message.requests.length))
            message.requests = [];
          message.requests.push($root.Request.decode(reader, reader.uint32()));
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return RequestBatch;
}();
$root.Request = function() {
  function Request(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  Request.prototype.incomingMessages = null;
  Request.prototype.channelStats = null;
  Request.prototype.serverStats = null;
  var $oneOfFields;
  Object.defineProperty(Request.prototype, "command", {
    get: $util.oneOfGetter($oneOfFields = ["incomingMessages", "channelStats", "serverStats"]),
    set: $util.oneOfSetter($oneOfFields)
  });
  Request.create = function create(properties) {
    return new Request(properties);
  };
  Request.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.incomingMessages != null && message.hasOwnProperty("incomingMessages"))
      $root.IncomingMessagesRequest.encode(message.incomingMessages, writer.uint32(
        /* id 1, wireType 2 =*/
        10
      ).fork()).ldelim();
    if (message.channelStats != null && message.hasOwnProperty("channelStats"))
      $root.ChannelStatsRequest.encode(message.channelStats, writer.uint32(
        /* id 2, wireType 2 =*/
        18
      ).fork()).ldelim();
    if (message.serverStats != null && message.hasOwnProperty("serverStats"))
      $root.ServerStatsRequest.encode(message.serverStats, writer.uint32(
        /* id 3, wireType 2 =*/
        26
      ).fork()).ldelim();
    return writer;
  };
  Request.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.Request();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.incomingMessages = $root.IncomingMessagesRequest.decode(reader, reader.uint32());
          break;
        case 2:
          message.channelStats = $root.ChannelStatsRequest.decode(reader, reader.uint32());
          break;
        case 3:
          message.serverStats = $root.ServerStatsRequest.decode(reader, reader.uint32());
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return Request;
}();
$root.IncomingMessagesRequest = function() {
  function IncomingMessagesRequest(properties) {
    this.messages = [];
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  IncomingMessagesRequest.prototype.messages = $util.emptyArray;
  IncomingMessagesRequest.create = function create(properties) {
    return new IncomingMessagesRequest(properties);
  };
  IncomingMessagesRequest.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.messages != null && message.messages.length)
      for (var i = 0; i < message.messages.length; ++i)
        $root.IncomingMessage.encode(message.messages[i], writer.uint32(
          /* id 1, wireType 2 =*/
          10
        ).fork()).ldelim();
    return writer;
  };
  IncomingMessagesRequest.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.IncomingMessagesRequest();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          if (!(message.messages && message.messages.length))
            message.messages = [];
          message.messages.push($root.IncomingMessage.decode(reader, reader.uint32()));
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return IncomingMessagesRequest;
}();
$root.IncomingMessage = function() {
  function IncomingMessage(properties) {
    this.receivers = [];
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  IncomingMessage.prototype.receivers = $util.emptyArray;
  IncomingMessage.prototype.sender = null;
  IncomingMessage.prototype.body = "";
  IncomingMessage.prototype.expiry = 0;
  IncomingMessage.prototype.type = "";
  IncomingMessage.create = function create(properties) {
    return new IncomingMessage(properties);
  };
  IncomingMessage.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.receivers != null && message.receivers.length)
      for (var i = 0; i < message.receivers.length; ++i)
        $root.Receiver.encode(message.receivers[i], writer.uint32(
          /* id 1, wireType 2 =*/
          10
        ).fork()).ldelim();
    if (message.sender != null && message.hasOwnProperty("sender"))
      $root.Sender.encode(message.sender, writer.uint32(
        /* id 2, wireType 2 =*/
        18
      ).fork()).ldelim();
    if (message.body != null && message.hasOwnProperty("body"))
      writer.uint32(
        /* id 3, wireType 2 =*/
        26
      ).string(message.body);
    if (message.expiry != null && message.hasOwnProperty("expiry"))
      writer.uint32(
        /* id 4, wireType 0 =*/
        32
      ).uint32(message.expiry);
    if (message.type != null && message.hasOwnProperty("type"))
      writer.uint32(
        /* id 5, wireType 2 =*/
        42
      ).string(message.type);
    return writer;
  };
  IncomingMessage.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.IncomingMessage();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          if (!(message.receivers && message.receivers.length))
            message.receivers = [];
          message.receivers.push($root.Receiver.decode(reader, reader.uint32()));
          break;
        case 2:
          message.sender = $root.Sender.decode(reader, reader.uint32());
          break;
        case 3:
          message.body = reader.string();
          break;
        case 4:
          message.expiry = reader.uint32();
          break;
        case 5:
          message.type = reader.string();
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return IncomingMessage;
}();
$root.ChannelStatsRequest = function() {
  function ChannelStatsRequest(properties) {
    this.channels = [];
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  ChannelStatsRequest.prototype.channels = $util.emptyArray;
  ChannelStatsRequest.create = function create(properties) {
    return new ChannelStatsRequest(properties);
  };
  ChannelStatsRequest.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.channels != null && message.channels.length)
      for (var i = 0; i < message.channels.length; ++i)
        $root.ChannelId.encode(message.channels[i], writer.uint32(
          /* id 1, wireType 2 =*/
          10
        ).fork()).ldelim();
    return writer;
  };
  ChannelStatsRequest.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.ChannelStatsRequest();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          if (!(message.channels && message.channels.length))
            message.channels = [];
          message.channels.push($root.ChannelId.decode(reader, reader.uint32()));
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return ChannelStatsRequest;
}();
$root.ChannelId = function() {
  function ChannelId(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  ChannelId.prototype.id = $util.newBuffer([]);
  ChannelId.prototype.isPrivate = false;
  ChannelId.prototype.signature = $util.newBuffer([]);
  ChannelId.create = function create(properties) {
    return new ChannelId(properties);
  };
  ChannelId.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.id != null && message.hasOwnProperty("id"))
      writer.uint32(
        /* id 1, wireType 2 =*/
        10
      ).bytes(message.id);
    if (message.isPrivate != null && message.hasOwnProperty("isPrivate"))
      writer.uint32(
        /* id 2, wireType 0 =*/
        16
      ).bool(message.isPrivate);
    if (message.signature != null && message.hasOwnProperty("signature"))
      writer.uint32(
        /* id 3, wireType 2 =*/
        26
      ).bytes(message.signature);
    return writer;
  };
  ChannelId.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.ChannelId();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.id = reader.bytes();
          break;
        case 2:
          message.isPrivate = reader.bool();
          break;
        case 3:
          message.signature = reader.bytes();
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return ChannelId;
}();
$root.ServerStatsRequest = function() {
  function ServerStatsRequest(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  ServerStatsRequest.create = function create(properties) {
    return new ServerStatsRequest(properties);
  };
  ServerStatsRequest.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    return writer;
  };
  ServerStatsRequest.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.ServerStatsRequest();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return ServerStatsRequest;
}();
$root.Sender = function() {
  function Sender(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  Sender.prototype.type = 0;
  Sender.prototype.id = $util.newBuffer([]);
  Sender.create = function create(properties) {
    return new Sender(properties);
  };
  Sender.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.type != null && message.hasOwnProperty("type"))
      writer.uint32(
        /* id 1, wireType 0 =*/
        8
      ).int32(message.type);
    if (message.id != null && message.hasOwnProperty("id"))
      writer.uint32(
        /* id 2, wireType 2 =*/
        18
      ).bytes(message.id);
    return writer;
  };
  Sender.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.Sender();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.type = reader.int32();
          break;
        case 2:
          message.id = reader.bytes();
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return Sender;
}();
$root.SenderType = function() {
  var valuesById = {}, values = Object.create(valuesById);
  values[valuesById[0] = "UNKNOWN"] = 0;
  values[valuesById[1] = "CLIENT"] = 1;
  values[valuesById[2] = "BACKEND"] = 2;
  return values;
}();
$root.Receiver = function() {
  function Receiver(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  Receiver.prototype.id = $util.newBuffer([]);
  Receiver.prototype.isPrivate = false;
  Receiver.prototype.signature = $util.newBuffer([]);
  Receiver.create = function create(properties) {
    return new Receiver(properties);
  };
  Receiver.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.id != null && message.hasOwnProperty("id"))
      writer.uint32(
        /* id 1, wireType 2 =*/
        10
      ).bytes(message.id);
    if (message.isPrivate != null && message.hasOwnProperty("isPrivate"))
      writer.uint32(
        /* id 2, wireType 0 =*/
        16
      ).bool(message.isPrivate);
    if (message.signature != null && message.hasOwnProperty("signature"))
      writer.uint32(
        /* id 3, wireType 2 =*/
        26
      ).bytes(message.signature);
    return writer;
  };
  Receiver.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.Receiver();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.id = reader.bytes();
          break;
        case 2:
          message.isPrivate = reader.bool();
          break;
        case 3:
          message.signature = reader.bytes();
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return Receiver;
}();
$root.ResponseBatch = function() {
  function ResponseBatch(properties) {
    this.responses = [];
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  ResponseBatch.prototype.responses = $util.emptyArray;
  ResponseBatch.create = function create(properties) {
    return new ResponseBatch(properties);
  };
  ResponseBatch.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.responses != null && message.responses.length)
      for (var i = 0; i < message.responses.length; ++i)
        $root.Response.encode(message.responses[i], writer.uint32(
          /* id 1, wireType 2 =*/
          10
        ).fork()).ldelim();
    return writer;
  };
  ResponseBatch.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.ResponseBatch();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          if (!(message.responses && message.responses.length))
            message.responses = [];
          message.responses.push($root.Response.decode(reader, reader.uint32()));
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return ResponseBatch;
}();
$root.Response = function() {
  function Response(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  Response.prototype.outgoingMessages = null;
  Response.prototype.channelStats = null;
  Response.prototype.serverStats = null;
  var $oneOfFields;
  Object.defineProperty(Response.prototype, "command", {
    get: $util.oneOfGetter($oneOfFields = ["outgoingMessages", "channelStats", "serverStats"]),
    set: $util.oneOfSetter($oneOfFields)
  });
  Response.create = function create(properties) {
    return new Response(properties);
  };
  Response.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.outgoingMessages != null && message.hasOwnProperty("outgoingMessages"))
      $root.OutgoingMessagesResponse.encode(message.outgoingMessages, writer.uint32(
        /* id 1, wireType 2 =*/
        10
      ).fork()).ldelim();
    if (message.channelStats != null && message.hasOwnProperty("channelStats"))
      $root.ChannelStatsResponse.encode(message.channelStats, writer.uint32(
        /* id 2, wireType 2 =*/
        18
      ).fork()).ldelim();
    if (message.serverStats != null && message.hasOwnProperty("serverStats"))
      $root.JsonResponse.encode(message.serverStats, writer.uint32(
        /* id 3, wireType 2 =*/
        26
      ).fork()).ldelim();
    return writer;
  };
  Response.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.Response();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.outgoingMessages = $root.OutgoingMessagesResponse.decode(reader, reader.uint32());
          break;
        case 2:
          message.channelStats = $root.ChannelStatsResponse.decode(reader, reader.uint32());
          break;
        case 3:
          message.serverStats = $root.JsonResponse.decode(reader, reader.uint32());
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return Response;
}();
$root.OutgoingMessagesResponse = function() {
  function OutgoingMessagesResponse(properties) {
    this.messages = [];
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  OutgoingMessagesResponse.prototype.messages = $util.emptyArray;
  OutgoingMessagesResponse.create = function create(properties) {
    return new OutgoingMessagesResponse(properties);
  };
  OutgoingMessagesResponse.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.messages != null && message.messages.length)
      for (var i = 0; i < message.messages.length; ++i)
        $root.OutgoingMessage.encode(message.messages[i], writer.uint32(
          /* id 1, wireType 2 =*/
          10
        ).fork()).ldelim();
    return writer;
  };
  OutgoingMessagesResponse.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.OutgoingMessagesResponse();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          if (!(message.messages && message.messages.length))
            message.messages = [];
          message.messages.push($root.OutgoingMessage.decode(reader, reader.uint32()));
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return OutgoingMessagesResponse;
}();
$root.OutgoingMessage = function() {
  function OutgoingMessage(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  OutgoingMessage.prototype.id = $util.newBuffer([]);
  OutgoingMessage.prototype.body = "";
  OutgoingMessage.prototype.expiry = 0;
  OutgoingMessage.prototype.created = 0;
  OutgoingMessage.prototype.sender = null;
  OutgoingMessage.create = function create(properties) {
    return new OutgoingMessage(properties);
  };
  OutgoingMessage.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.id != null && message.hasOwnProperty("id"))
      writer.uint32(
        /* id 1, wireType 2 =*/
        10
      ).bytes(message.id);
    if (message.body != null && message.hasOwnProperty("body"))
      writer.uint32(
        /* id 2, wireType 2 =*/
        18
      ).string(message.body);
    if (message.expiry != null && message.hasOwnProperty("expiry"))
      writer.uint32(
        /* id 3, wireType 0 =*/
        24
      ).uint32(message.expiry);
    if (message.created != null && message.hasOwnProperty("created"))
      writer.uint32(
        /* id 4, wireType 5 =*/
        37
      ).fixed32(message.created);
    if (message.sender != null && message.hasOwnProperty("sender"))
      $root.Sender.encode(message.sender, writer.uint32(
        /* id 5, wireType 2 =*/
        42
      ).fork()).ldelim();
    return writer;
  };
  OutgoingMessage.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.OutgoingMessage();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.id = reader.bytes();
          break;
        case 2:
          message.body = reader.string();
          break;
        case 3:
          message.expiry = reader.uint32();
          break;
        case 4:
          message.created = reader.fixed32();
          break;
        case 5:
          message.sender = $root.Sender.decode(reader, reader.uint32());
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return OutgoingMessage;
}();
$root.ChannelStatsResponse = function() {
  function ChannelStatsResponse(properties) {
    this.channels = [];
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  ChannelStatsResponse.prototype.channels = $util.emptyArray;
  ChannelStatsResponse.create = function create(properties) {
    return new ChannelStatsResponse(properties);
  };
  ChannelStatsResponse.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.channels != null && message.channels.length)
      for (var i = 0; i < message.channels.length; ++i)
        $root.ChannelStats.encode(message.channels[i], writer.uint32(
          /* id 1, wireType 2 =*/
          10
        ).fork()).ldelim();
    return writer;
  };
  ChannelStatsResponse.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.ChannelStatsResponse();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          if (!(message.channels && message.channels.length))
            message.channels = [];
          message.channels.push($root.ChannelStats.decode(reader, reader.uint32()));
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return ChannelStatsResponse;
}();
$root.ChannelStats = function() {
  function ChannelStats(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  ChannelStats.prototype.id = $util.newBuffer([]);
  ChannelStats.prototype.isPrivate = false;
  ChannelStats.prototype.isOnline = false;
  ChannelStats.create = function create(properties) {
    return new ChannelStats(properties);
  };
  ChannelStats.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.id != null && message.hasOwnProperty("id"))
      writer.uint32(
        /* id 1, wireType 2 =*/
        10
      ).bytes(message.id);
    if (message.isPrivate != null && message.hasOwnProperty("isPrivate"))
      writer.uint32(
        /* id 2, wireType 0 =*/
        16
      ).bool(message.isPrivate);
    if (message.isOnline != null && message.hasOwnProperty("isOnline"))
      writer.uint32(
        /* id 3, wireType 0 =*/
        24
      ).bool(message.isOnline);
    return writer;
  };
  ChannelStats.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.ChannelStats();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.id = reader.bytes();
          break;
        case 2:
          message.isPrivate = reader.bool();
          break;
        case 3:
          message.isOnline = reader.bool();
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return ChannelStats;
}();
$root.JsonResponse = function() {
  function JsonResponse(properties) {
    if (properties) {
      for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
        if (properties[keys[i]] != null)
          this[keys[i]] = properties[keys[i]];
    }
  }
  JsonResponse.prototype.json = "";
  JsonResponse.create = function create(properties) {
    return new JsonResponse(properties);
  };
  JsonResponse.encode = function encode(message, writer) {
    if (!writer)
      writer = $Writer.create();
    if (message.json != null && message.hasOwnProperty("json"))
      writer.uint32(
        /* id 1, wireType 2 =*/
        10
      ).string(message.json);
    return writer;
  };
  JsonResponse.decode = function decode(reader, length) {
    if (!(reader instanceof $Reader))
      reader = $Reader.create(reader);
    var end = length === void 0 ? reader.len : reader.pos + length, message = new $root.JsonResponse();
    while (reader.pos < end) {
      var tag = reader.uint32();
      switch (tag >>> 3) {
        case 1:
          message.json = reader.string();
          break;
        default:
          reader.skipType(tag & 7);
          break;
      }
    }
    return message;
  };
  return JsonResponse;
}();

$root["Response"];
const ResponseBatch = $root["ResponseBatch"];
$root["Request"];
const RequestBatch = $root["RequestBatch"];
$root["IncomingMessagesRequest"];
const IncomingMessage = $root["IncomingMessage"];
const Receiver = $root["Receiver"];

class AbstractConnector {
  _logger = null;
  _connected = false;
  _connectionType;
  _disconnectCode = 0;
  _disconnectReason = "";
  _parent;
  _callbacks;
  constructor(config) {
    this._parent = config.parent;
    this._connectionType = ConnectionType.Undefined;
    this._callbacks = {
      onOpen: Type.isFunction(config.onOpen) ? config.onOpen : () => {
      },
      onDisconnect: Type.isFunction(config.onDisconnect) ? config.onDisconnect : () => {
      },
      onError: Type.isFunction(config.onError) ? config.onError : () => {
      },
      onMessage: Type.isFunction(config.onMessage) ? config.onMessage : () => {
      }
    };
  }
  setLogger(logger) {
    this._logger = logger;
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  destroy() {
  }
  get connected() {
    return this._connected;
  }
  set connected(value) {
    if (value == this._connected) {
      return;
    }
    this._connected = value;
    if (this._connected) {
      this._callbacks.onOpen();
    } else {
      this._callbacks.onDisconnect({
        code: this.disconnectCode,
        reason: this.disconnectReason
      });
    }
  }
  get disconnectCode() {
    return this._disconnectCode;
  }
  get disconnectReason() {
    return this._disconnectReason;
  }
  get connectionPath() {
    return this._parent.getConnectionPath(
      this._connectionType
    );
  }
}

class WebSocketConnector extends AbstractConnector {
  _socket;
  _onSocketOpenHandler;
  _onSocketCloseHandler;
  _onSocketErrorHandler;
  _onSocketMessageHandler;
  constructor(config) {
    super(config);
    this._connectionType = ConnectionType.WebSocket;
    this._socket = null;
    this._onSocketOpenHandler = this._onSocketOpen.bind(this);
    this._onSocketCloseHandler = this._onSocketClose.bind(this);
    this._onSocketErrorHandler = this._onSocketError.bind(this);
    this._onSocketMessageHandler = this._onSocketMessage.bind(this);
  }
  destroy() {
    super.destroy();
    if (this._socket) {
      this._socket.close();
      this._socket = null;
    }
  }
  /**
   * @inheritDoc
   */
  connect() {
    if (this._socket) {
      if (this._socket.readyState === 1) {
        return;
      } else {
        this.clearEventListener();
        this._socket.close();
        this._socket = null;
      }
    }
    this._createSocket();
  }
  get socket() {
    return this._socket;
  }
  /**
   * @inheritDoc
   * @param code
   * @param reason
   */
  disconnect(code, reason) {
    if (this._socket !== null) {
      this.clearEventListener();
      this._socket.close(
        code,
        reason
      );
    }
    this._socket = null;
    this._disconnectCode = code;
    this._disconnectReason = reason;
    this.connected = false;
  }
  /**
   * Via websocket connection
   * @inheritDoc
   */
  send(buffer) {
    if (!this._socket || this._socket.readyState !== 1) {
      this.getLogger().error(new Error(
        `${Text.getDateForLog()}: Pull: WebSocket is not connected`
      ));
      return false;
    }
    this._socket.send(buffer);
    return true;
  }
  // region Event Handlers ////
  _onSocketOpen() {
    this.connected = true;
  }
  _onSocketClose(event) {
    this._socket = null;
    this._disconnectCode = Number(event.code);
    this._disconnectReason = event.reason;
    this.connected = false;
  }
  _onSocketError(event) {
    this._callbacks.onError(new Error(
      `Socket error: ${event}`
    ));
  }
  _onSocketMessage(event) {
    this._callbacks.onMessage(
      event.data
    );
  }
  // endregion ////
  // region Tools ////
  clearEventListener() {
    if (this._socket) {
      this._socket.removeEventListener(
        "open",
        this._onSocketOpenHandler
      );
      this._socket.removeEventListener(
        "close",
        this._onSocketCloseHandler
      );
      this._socket.removeEventListener(
        "error",
        this._onSocketErrorHandler
      );
      this._socket.removeEventListener(
        "message",
        this._onSocketMessageHandler
      );
    }
  }
  _createSocket() {
    if (this._socket) {
      throw new Error("Socket already exists");
    }
    if (!this.connectionPath) {
      throw new Error("Websocket connection path is not defined");
    }
    this._socket = new WebSocket(this.connectionPath);
    this._socket.binaryType = "arraybuffer";
    this._socket.addEventListener(
      "open",
      this._onSocketOpenHandler
    );
    this._socket.addEventListener(
      "close",
      this._onSocketCloseHandler
    );
    this._socket.addEventListener(
      "error",
      this._onSocketErrorHandler
    );
    this._socket.addEventListener(
      "message",
      this._onSocketMessageHandler
    );
  }
  // endregion ////
}

const LONG_POLLING_TIMEOUT = 60;
class LongPollingConnector extends AbstractConnector {
  _active;
  _requestTimeout;
  _failureTimeout;
  _xhr;
  _requestAborted;
  constructor(config) {
    super(config);
    this._active = false;
    this._connectionType = ConnectionType.LongPolling;
    this._requestTimeout = null;
    this._failureTimeout = null;
    this._xhr = this.createXhr();
    this._requestAborted = false;
  }
  /**
   * @inheritDoc
   */
  connect() {
    this._active = true;
    this.performRequest();
  }
  /**
   * @inheritDoc
   * @param code
   * @param reason
   */
  disconnect(code, reason) {
    this._active = false;
    this.clearTimeOut();
    if (this._xhr) {
      this._requestAborted = true;
      this._xhr.abort();
    }
    this._disconnectCode = code;
    this._disconnectReason = reason;
    this.connected = false;
  }
  performRequest() {
    if (!this._active) {
      return;
    }
    if (!this.connectionPath) {
      throw new Error("Long polling connection path is not defined");
    }
    if (this._xhr.readyState !== 0 && this._xhr.readyState !== 4) {
      return;
    }
    this.clearTimeOut();
    this._failureTimeout = setTimeout(
      () => {
        this.connected = true;
      },
      5e3
    );
    this._requestTimeout = setTimeout(
      this.onRequestTimeout.bind(this),
      LONG_POLLING_TIMEOUT * 1e3
    );
    this._xhr.open("GET", this.connectionPath);
    this._xhr.send();
  }
  onRequestTimeout() {
    this._requestAborted = true;
    this._xhr.abort();
    this.performRequest();
  }
  onXhrReadyStateChange() {
    if (this._xhr.readyState === 4) {
      if (!this._requestAborted || this._xhr.status == 200) {
        this.onResponse(this._xhr.response);
      }
      this._requestAborted = false;
    }
  }
  /**
   * Via http request
   * @inheritDoc
   */
  send(buffer) {
    const path = this._parent.getPublicationPath();
    if (!path) {
      this.getLogger().error(new Error(
        `${Text.getDateForLog()}: Pull: publication path is empty`
      ));
      return false;
    }
    const xhr = new XMLHttpRequest();
    xhr.open("POST", path);
    xhr.send(buffer);
    return true;
  }
  onResponse(response) {
    this.clearTimeOut();
    if (this._xhr.status === 200) {
      this.connected = true;
      if (Type.isStringFilled(response) || response instanceof ArrayBuffer) {
        this._callbacks.onMessage(response);
      } else {
        this._parent.session.mid = null;
      }
      this.performRequest();
    } else if (this._xhr.status === 304) {
      this.connected = true;
      if (this._xhr.getResponseHeader("Expires") === "Thu, 01 Jan 1973 11:11:01 GMT") {
        const lastMessageId = this._xhr.getResponseHeader("Last-Message-Id");
        if (Type.isStringFilled(lastMessageId)) {
          this._parent.setLastMessageId(lastMessageId || "");
        }
      }
      this.performRequest();
    } else {
      this._callbacks.onError(new Error(
        "Could not connect to the server"
      ));
      this.connected = false;
    }
  }
  // region Tools ////
  clearTimeOut() {
    if (this._failureTimeout) {
      clearTimeout(this._failureTimeout);
      this._failureTimeout = null;
    }
    if (this._requestTimeout) {
      clearTimeout(this._requestTimeout);
      this._requestTimeout = null;
    }
  }
  createXhr() {
    const result = new XMLHttpRequest();
    if (this._parent.isProtobufSupported() && !this._parent.isJsonRpc()) {
      result.responseType = "arraybuffer";
    }
    result.addEventListener(
      "readystatechange",
      this.onXhrReadyStateChange.bind(this)
    );
    return result;
  }
  // endregion ////
}

const REVISION = 19;
const RESTORE_WEBSOCKET_TIMEOUT = 30 * 60;
const OFFLINE_STATUS_DELAY = 5e3;
const CONFIG_CHECK_INTERVAL = 60 * 1e3;
const MAX_IDS_TO_STORE = 10;
const PING_TIMEOUT = 10;
const JSON_RPC_PING = "ping";
const JSON_RPC_PONG = "pong";
const LS_SESSION = "bx-pull-session";
const LS_SESSION_CACHE_TIME = 20;
const EmptyConfig = {
  api: {},
  channels: {},
  publicChannels: {},
  server: { timeShift: 0 },
  clientId: null,
  jwt: null,
  exp: 0
};
class PullClient {
  // region Params ////
  _logger = null;
  _restClient;
  _status;
  _context;
  _guestMode;
  _guestUserId;
  _userId;
  _configGetMethod;
  _getPublicListMethod;
  _siteId;
  _enabled;
  _unloading = false;
  _starting = false;
  _debug = false;
  _connectionAttempt = 0;
  _connectionType = ConnectionType.WebSocket;
  _reconnectTimeout = null;
  _restartTimeout = null;
  _restoreWebSocketTimeout = null;
  _skipStorageInit;
  _skipCheckRevision;
  _subscribers = {};
  _watchTagsQueue = /* @__PURE__ */ new Map();
  _watchUpdateInterval = 174e4;
  _watchForceUpdateInterval = 5e3;
  _configTimestamp = 0;
  _session = {
    mid: null,
    tag: null,
    time: null,
    history: {},
    lastMessageIds: [],
    messageCount: 0
  };
  _connectors = {
    [ConnectionType.Undefined]: null,
    [ConnectionType.WebSocket]: null,
    [ConnectionType.LongPolling]: null
  };
  _isSecure;
  _config = null;
  _storage = null;
  _sharedConfig;
  _channelManager;
  _jsonRpcAdapter = null;
  /**
   * @depricate
   * @private
   */
  // private _notificationPopup: null = null
  // timers ////
  _checkInterval = null;
  _offlineTimeout = null;
  _watchUpdateTimeout = null;
  _pingWaitTimeout = null;
  // manual stop workaround ////
  _isManualDisconnect = false;
  _loggingEnabled = false;
  // bound event handlers ////
  _onPingTimeoutHandler;
  // [userId] => array of callbacks
  _userStatusCallbacks = {};
  _connectPromise = null;
  _startingPromise = null;
  // endregion ////
  // region Init ////
  /**
   * @done
   * @param params
   */
  constructor(params) {
    this._restClient = params.b24;
    this._status = PullStatus.Offline;
    this._context = "master";
    if (params.restApplication) {
      if (typeof params.configGetMethod === "undefined") {
        params.configGetMethod = "pull.application.config.get";
      }
      if (typeof params.skipCheckRevision === "undefined") {
        params.skipCheckRevision = true;
      }
      if (Type.isStringFilled(params.restApplication)) {
        params.siteId = params.restApplication;
      }
      params.serverEnabled = true;
    }
    this._guestMode = params.guestMode ? Text.toBoolean(params.guestMode) : false;
    this._guestUserId = params.guestUserId ? Text.toInteger(params.guestUserId) : 0;
    if (this._guestMode && this._guestUserId > 0) {
      this._userId = this._guestUserId;
    } else {
      this._guestMode = false;
      this._userId = params.userId ? Text.toInteger(params.userId) : 0;
    }
    this._siteId = params.siteId ? params.siteId : "none";
    this._enabled = !Type.isUndefined(params.serverEnabled) ? params.serverEnabled === true : true;
    this._configGetMethod = !Type.isStringFilled(params.configGetMethod) ? "pull.config.get" : params.configGetMethod || "";
    this._getPublicListMethod = !Type.isStringFilled(params.getPublicListMethod) ? "pull.channel.public.list" : params.getPublicListMethod || "";
    this._skipStorageInit = params.skipStorageInit === true;
    this._skipCheckRevision = params.skipCheckRevision === true;
    if (!Type.isUndefined(params.configTimestamp)) {
      this._configTimestamp = Text.toInteger(params.configTimestamp);
    }
    this._isSecure = document?.location.href.indexOf("https") === 0;
    if (this._userId && !this._skipStorageInit) {
      this._storage = new StorageManager({
        userId: this._userId,
        siteId: this._siteId
      });
    }
    this._sharedConfig = new SharedConfig({
      onWebSocketBlockChanged: this.onWebSocketBlockChanged.bind(this),
      storage: this._storage
    });
    this._channelManager = new ChannelManager({
      b24: this._restClient,
      getPublicListMethod: this._getPublicListMethod
    });
    this._loggingEnabled = this._sharedConfig.isLoggingEnabled();
    this._onPingTimeoutHandler = this.onPingTimeout.bind(this);
  }
  setLogger(logger) {
    this._logger = logger;
    this._jsonRpcAdapter?.setLogger(this.getLogger());
    this._storage?.setLogger(this.getLogger());
    this._sharedConfig.setLogger(this.getLogger());
    this._channelManager.setLogger(this.getLogger());
    this._connectors.webSocket?.setLogger(this.getLogger());
    this._connectors.longPolling?.setLogger(this.getLogger());
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(
        `NullLogger`
      );
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  destroy() {
    this.stop(
      CloseReasons.NORMAL_CLOSURE,
      "manual stop"
    );
    this.onBeforeUnload();
  }
  /**
   * @done
   * @private
   */
  init() {
    this._connectors.webSocket = new WebSocketConnector({
      parent: this,
      onOpen: this.onWebSocketOpen.bind(this),
      onMessage: this.onIncomingMessage.bind(this),
      onDisconnect: this.onWebSocketDisconnect.bind(this),
      onError: this.onWebSocketError.bind(this)
    });
    this._connectors.longPolling = new LongPollingConnector({
      parent: this,
      onOpen: this.onLongPollingOpen.bind(this),
      onMessage: this.onIncomingMessage.bind(this),
      onDisconnect: this.onLongPollingDisconnect.bind(this),
      onError: this.onLongPollingError.bind(this)
    });
    this._connectionType = this.isWebSocketAllowed() ? ConnectionType.WebSocket : ConnectionType.LongPolling;
    window.addEventListener("beforeunload", this.onBeforeUnload.bind(this));
    window.addEventListener("offline", this.onOffline.bind(this));
    window.addEventListener("online", this.onOnline.bind(this));
    this._jsonRpcAdapter = new JsonRpc({
      connector: this._connectors.webSocket,
      handlers: {
        "incoming.message": this.handleRpcIncomingMessage.bind(this)
      }
    });
  }
  // endregion ////
  // region Get-Set ////
  /**
   * @done
   */
  get connector() {
    return this._connectors[this._connectionType];
  }
  /**
   * @done
   */
  get status() {
    return this._status;
  }
  /**
   * @done
   * @param status
   */
  set status(status) {
    if (this._status === status) {
      return;
    }
    this._status = status;
    if (this._offlineTimeout) {
      clearTimeout(this._offlineTimeout);
      this._offlineTimeout = null;
    }
    if (status === PullStatus.Offline) {
      this.sendPullStatusDelayed(status, OFFLINE_STATUS_DELAY);
    } else {
      this.sendPullStatus(status);
    }
  }
  get session() {
    return this._session;
  }
  // endregion ////
  // region Public /////
  /**
   * @done
   * Creates a subscription to incoming messages.
   *
   * @param {TypeSubscriptionOptions | TypeSubscriptionCommandHandler} params
   * @returns {Function} - Unsubscribe callback function
   */
  subscribe(params) {
    if (!Type.isPlainObject(params)) {
      return this.attachCommandHandler(params);
    }
    params = params;
    params.type = params.type || SubscriptionType.Server;
    params.command = params.command || null;
    if (params.type == SubscriptionType.Server || params.type == SubscriptionType.Client) {
      if (typeof params.moduleId === "undefined") {
        throw new Error(`${Text.getDateForLog()}: Pull.subscribe: parameter moduleId is not specified`);
      }
      if (typeof this._subscribers[params.type] === "undefined") {
        this._subscribers[params.type] = {};
      }
      if (typeof this._subscribers[params.type][params.moduleId] === "undefined") {
        this._subscribers[params.type][params.moduleId] = {
          callbacks: [],
          commands: {}
        };
      }
      if (params.command) {
        if (typeof this._subscribers[params.type][params.moduleId]["commands"][params.command] === "undefined") {
          this._subscribers[params.type][params.moduleId]["commands"][params.command] = [];
        }
        this._subscribers[params.type][params.moduleId]["commands"][params.command].push(params.callback);
        return () => {
          if (typeof params.type === "undefined" || typeof params.moduleId === "undefined" || typeof params.command === "undefined" || null === params.command) {
            return;
          }
          this._subscribers[params.type][params.moduleId]["commands"][params.command] = this._subscribers[params.type][params.moduleId]["commands"][params.command].filter((element) => {
            return element !== params.callback;
          });
        };
      } else {
        this._subscribers[params.type][params.moduleId]["callbacks"].push(params.callback);
        return () => {
          if (typeof params.type === "undefined" || typeof params.moduleId === "undefined") {
            return;
          }
          this._subscribers[params.type][params.moduleId]["callbacks"] = this._subscribers[params.type][params.moduleId]["callbacks"].filter((element) => {
            return element !== params.callback;
          });
        };
      }
    } else {
      if (typeof this._subscribers[params.type] === "undefined") {
        this._subscribers[params.type] = [];
      }
      this._subscribers[params.type].push(params.callback);
      return () => {
        if (typeof params.type === "undefined") {
          return;
        }
        this._subscribers[params.type] = this._subscribers[params.type].filter((element) => {
          return element !== params.callback;
        });
      };
    }
  }
  /**
   * @done
   * @param {TypeSubscriptionCommandHandler} handler
   * @returns {Function} - Unsubscribe callback function
   */
  attachCommandHandler(handler) {
    if (typeof handler.getModuleId !== "function" || typeof handler.getModuleId() !== "string") {
      this.getLogger().error(`${Text.getDateForLog()}: Pull.attachCommandHandler: result of handler.getModuleId() is not a string.`);
      return () => {
      };
    }
    let type = SubscriptionType.Server;
    if (typeof handler.getSubscriptionType === "function") {
      type = handler.getSubscriptionType();
    }
    return this.subscribe({
      type,
      moduleId: handler.getModuleId(),
      callback: (data) => {
        let method = null;
        if (typeof handler.getMap === "function") {
          const mapping = handler.getMap();
          if (mapping && typeof mapping === "object") {
            const rowMapping = mapping[data.command];
            if (typeof rowMapping === "function") {
              method = rowMapping.bind(handler);
            } else if (typeof rowMapping === "string" && typeof handler[rowMapping] === "function") {
              method = handler[rowMapping].bind(handler);
            }
          }
        }
        if (!method) {
          const methodName = `handle${Text.capitalize(data.command)}`;
          if (typeof handler[methodName] === "function") {
            method = handler[methodName].bind(handler);
          }
        }
        if (method) {
          if (this._debug && this._context !== "master") {
            this.getLogger().warn(
              `${Text.getDateForLog()}: Pull.attachCommandHandler: result of handler.getModuleId() is not a string`,
              data
            );
          }
          method(
            data.params,
            data.extra,
            data.command
          );
        }
      }
    });
  }
  /**
   * @done
   * @param config
   */
  async start(config = null) {
    let allowConfigCaching = true;
    if (this.isConnected()) {
      return Promise.resolve(true);
    }
    if (this._starting && this._startingPromise) {
      return this._startingPromise;
    }
    if (!this._userId) {
      throw new Error("Not set userId");
    }
    if (this._siteId === "none") {
      throw new Error("Not set siteId");
    }
    let skipReconnectToLastSession = false;
    if (!!config && Type.isPlainObject(config)) {
      if (typeof config?.skipReconnectToLastSession !== "undefined") {
        skipReconnectToLastSession = config.skipReconnectToLastSession;
        delete config.skipReconnectToLastSession;
      }
      this._config = config;
      allowConfigCaching = false;
    }
    if (!this._enabled) {
      return Promise.reject({
        ex: {
          error: "PULL_DISABLED",
          error_description: "Push & Pull server is disabled"
        }
      });
    }
    const now = (/* @__PURE__ */ new Date()).getTime();
    let oldSession;
    if (!skipReconnectToLastSession && this._storage) {
      oldSession = this._storage.get(LS_SESSION, null);
    }
    if (Type.isPlainObject(oldSession) && oldSession.hasOwnProperty("ttl") && oldSession.ttl >= now) {
      this._session.mid = oldSession.mid;
    }
    this._starting = true;
    return this._startingPromise = new Promise((resolve, reject) => {
      this.loadConfig("client_start").then((config2) => {
        this.setConfig(
          config2,
          allowConfigCaching
        );
        this.init();
        this.updateWatch(true);
        this.startCheckConfig();
        this.connect().then(
          () => resolve(true),
          (error) => reject(error)
        );
      }).catch((error) => {
        this._starting = false;
        this.status = PullStatus.Offline;
        this.stopCheckConfig();
        this.getLogger().error(`${Text.getDateForLog()}: Pull: could not read push-server config `, error);
        reject(error);
      });
    });
  }
  /**
   * @done
   * @param disconnectCode
   * @param disconnectReason
   */
  restart(disconnectCode = CloseReasons.NORMAL_CLOSURE, disconnectReason = "manual restart") {
    if (this._restartTimeout) {
      clearTimeout(this._restartTimeout);
      this._restartTimeout = null;
    }
    this.getLogger().log(`${Text.getDateForLog()}: Pull: restarting with code ${disconnectCode}`);
    this.disconnect(
      disconnectCode,
      disconnectReason
    );
    if (this._storage) {
      this._storage.remove(LsKeys.PullConfig);
    }
    this._config = null;
    const loadConfigReason = `${disconnectCode}_${disconnectReason.replaceAll(" ", "_")}`;
    this.loadConfig(loadConfigReason).then(
      (config) => {
        this.setConfig(config, true);
        this.updateWatch();
        this.startCheckConfig();
        this.connect().catch((error) => {
          this.getLogger().error(error);
        });
      },
      (error) => {
        this.getLogger().error(`${Text.getDateForLog()}: Pull: could not read push-server config `, error);
        this.status = PullStatus.Offline;
        if (this._reconnectTimeout) {
          clearTimeout(this._reconnectTimeout);
          this._reconnectTimeout = null;
        }
        if (error?.status == 401 || error?.status == 403) {
          this.stopCheckConfig();
          this.onCustomEvent("onPullError", ["AUTHORIZE_ERROR"]);
        }
      }
    );
  }
  /**
   * @done
   */
  stop(disconnectCode = CloseReasons.NORMAL_CLOSURE, disconnectReason = "manual stop") {
    this.disconnect(
      disconnectCode,
      disconnectReason
    );
    this.stopCheckConfig();
  }
  /**
   * @done
   */
  reconnect(disconnectCode, disconnectReason, delay = 1) {
    this.disconnect(
      disconnectCode,
      disconnectReason
    );
    this.scheduleReconnect(
      delay
    );
  }
  /**
   * @done
   * @param lastMessageId
   */
  setLastMessageId(lastMessageId) {
    this._session.mid = lastMessageId;
  }
  /**
   * @done
   *
   * Send single message to the specified users.
   *
   * @param users User ids of the message receivers.
   * @param moduleId Name of the module to receive message,
   * @param command Command name.
   * @param {object} params Command parameters.
   * @param [expiry] Message expiry time in seconds.
   * @return {Promise}
   */
  async sendMessage(users, moduleId, command, params, expiry) {
    const message = {
      userList: users,
      body: {
        module_id: moduleId,
        command,
        params
      },
      expiry
    };
    if (this.isJsonRpc()) {
      return this._jsonRpcAdapter?.executeOutgoingRpcCommand(
        RpcMethod.Publish,
        message
      );
    } else {
      return this.sendMessageBatch([message]);
    }
  }
  /**
   * @done
   * Send single message to the specified public channels.
   *
   * @param  publicChannels Public ids of the channels to receive message.
   * @param moduleId Name of the module to receive message,
   * @param command Command name.
   * @param {object} params Command parameters.
   * @param [expiry] Message expiry time in seconds.
   * @return {Promise}
   */
  async sendMessageToChannels(publicChannels, moduleId, command, params, expiry) {
    const message = {
      channelList: publicChannels,
      body: {
        module_id: moduleId,
        command,
        params
      },
      expiry
    };
    if (this.isJsonRpc()) {
      return this._jsonRpcAdapter?.executeOutgoingRpcCommand(
        RpcMethod.Publish,
        message
      );
    } else {
      return this.sendMessageBatch([message]);
    }
  }
  /**
   * @done
   * @param debugFlag
   */
  capturePullEvent(debugFlag = true) {
    this._debug = debugFlag;
  }
  /**
   * @done
   * @param loggingFlag
   */
  enableLogging(loggingFlag = true) {
    this._sharedConfig.setLoggingEnabled(loggingFlag);
    this._loggingEnabled = loggingFlag;
  }
  /**
   * Returns list channels that the connection is subscribed to.
   *
   * @returns {Promise}
   */
  async listChannels() {
    return this._jsonRpcAdapter?.executeOutgoingRpcCommand(
      RpcMethod.ListChannels,
      {}
    ) || Promise.reject(new Error("jsonRpcAdapter not init"));
  }
  /**
   * @done
   * Returns "last seen" time in seconds for the users. Result format: Object{userId: int}
   * If the user is currently connected - will return 0.
   * If the user if offline - will return diff between current timestamp and last seen timestamp in seconds.
   * If the user was never online - the record for user will be missing from the result object.
   *
   * @param {integer[]} userList List of user ids.
   * @returns {Promise}
   */
  async getUsersLastSeen(userList) {
    if (!Type.isArray(userList) || !userList.every((item) => typeof item === "number")) {
      throw new Error("userList must be an array of numbers");
    }
    let result = {};
    return new Promise((resolve, reject) => {
      this._jsonRpcAdapter?.executeOutgoingRpcCommand(
        RpcMethod.GetUsersLastSeen,
        {
          userList
        }
      ).then((response) => {
        let unresolved = [];
        for (let i = 0; i < userList.length; i++) {
          if (!response.hasOwnProperty(userList[i])) {
            unresolved.push(userList[i]);
          }
        }
        if (unresolved.length === 0) {
          return resolve(result);
        }
        const params = {
          userIds: unresolved,
          sendToQueueSever: true
        };
        this._restClient.callMethod(
          "pull.api.user.getLastSeen",
          params
        ).then((response2) => {
          let data = response2.getData().result;
          for (let userId in data) {
            result[Number(userId)] = Number(data[userId]);
          }
          return resolve(result);
        }).catch((error) => {
          this.getLogger().error(error);
          reject(error);
        });
      }).catch((error) => {
        this.getLogger().error(error);
        reject(error);
      });
    });
  }
  /**
   * @done
   * Pings server. In case of success promise will be resolved, otherwise - rejected.
   *
   * @param {number} timeout Request timeout in seconds
   * @returns {Promise}
   */
  async ping(timeout = 5) {
    return this._jsonRpcAdapter?.executeOutgoingRpcCommand(
      RpcMethod.Ping,
      {},
      timeout
    );
  }
  /**
   * @done
   * @param userId {number}
   * @param callback {UserStatusCallback}
   * @returns {Promise}
   */
  async subscribeUserStatusChange(userId, callback) {
    return new Promise((resolve, reject) => {
      this._jsonRpcAdapter?.executeOutgoingRpcCommand(
        RpcMethod.SubscribeStatusChange,
        {
          userId
        }
      ).then(() => {
        if (!this._userStatusCallbacks[userId]) {
          this._userStatusCallbacks[userId] = [];
        }
        if (Type.isFunction(callback)) {
          this._userStatusCallbacks[userId].push(callback);
        }
        return resolve();
      }).catch((err) => reject(err));
    });
  }
  /**
   * @done
   * @param {number} userId
   * @param {UserStatusCallback} callback
   * @returns {Promise}
   */
  async unsubscribeUserStatusChange(userId, callback) {
    if (this._userStatusCallbacks[userId]) {
      this._userStatusCallbacks[userId] = this._userStatusCallbacks[userId].filter(
        (cb) => cb !== callback
      );
      if (this._userStatusCallbacks[userId].length === 0) {
        return this._jsonRpcAdapter?.executeOutgoingRpcCommand(
          RpcMethod.UnsubscribeStatusChange,
          {
            userId
          }
        );
      }
    }
    return Promise.resolve();
  }
  // endregion ////
  // region Get ////
  /**
   * @done
   */
  getRevision() {
    return this._config && this._config.api ? this._config.api.revision_web : null;
  }
  /**
   * @done
   */
  getServerVersion() {
    return this._config && this._config.server ? this._config.server.version : 0;
  }
  /**
   * @done
   */
  getServerMode() {
    return this._config && this._config.server ? this._config.server.mode : null;
  }
  /**
   * @done
   */
  getConfig() {
    return this._config;
  }
  /**
   * @done
   */
  getDebugInfo() {
    if (!JSON || !JSON.stringify) {
      return {};
    }
    let configDump;
    if (this._config && this._config.channels) {
      configDump = {
        ChannelID: this._config.channels.private?.id || "n/a",
        ChannelDie: this._config.channels.private?.end || "n/a",
        ChannelDieShared: this._config.channels.shared?.end || "n/a"
      };
    } else {
      configDump = {
        ConfigError: "config is not loaded"
      };
    }
    let websocketMode = "-";
    if (this._connectors.webSocket && this._connectors.webSocket?.socket) {
      if (this.isJsonRpc()) {
        websocketMode = "json-rpc";
      } else {
        websocketMode = this._connectors.webSocket?.socket?.url.search("binaryMode=true") != -1 ? "protobuf" : "text";
      }
    }
    return {
      "UserId": this._userId + (this._userId > 0 ? "" : "(guest)"),
      "Guest userId": this._guestMode && this._guestUserId !== 0 ? this._guestUserId : "-",
      "Browser online": navigator.onLine ? "Y" : "N",
      "Connect": this.isConnected() ? "Y" : "N",
      "Server type": this.isSharedMode() ? "cloud" : "local",
      "WebSocket supported": this.isWebSocketSupported() ? "Y" : "N",
      "WebSocket connected": this._connectors.webSocket && this._connectors.webSocket.connected ? "Y" : "N",
      "WebSocket mode": websocketMode,
      "Try connect": this._reconnectTimeout ? "Y" : "N",
      "Try number": this._connectionAttempt,
      "Path": this.connector?.connectionPath || "-",
      ...configDump,
      "Last message": this._session.mid ? this._session.mid : "-",
      "Session history": this._session.history,
      "Watch tags": this._watchTagsQueue.entries()
    };
  }
  /**
   * @process
   * @param connectionType
   */
  getConnectionPath(connectionType) {
    let path;
    let params = {};
    switch (connectionType) {
      case ConnectionType.WebSocket:
        path = this._isSecure ? this._config?.server.websocket_secure : this._config?.server.websocket;
        break;
      case ConnectionType.LongPolling:
        path = this._isSecure ? this._config?.server.long_pooling_secure : this._config?.server.long_polling;
        break;
      default:
        throw new Error(`Unknown connection type ${connectionType}`);
    }
    if (!Type.isStringFilled(path)) {
      throw new Error(`Empty path`);
    }
    if (typeof this._config?.jwt === "string" && this._config?.jwt !== "") {
      params["token"] = this._config?.jwt;
    } else {
      let channels = [];
      if (this._config?.channels?.private) {
        channels.push(this._config.channels.private?.id || "");
      }
      if (this._config?.channels.private?.id) {
        channels.push(this._config.channels.private.id);
      }
      if (this._config?.channels.shared?.id) {
        channels.push(this._config.channels.shared.id);
      }
      if (channels.length === 0) {
        throw new Error(`Empty channels`);
      }
      params["CHANNEL_ID"] = channels.join("/");
    }
    if (this.isJsonRpc()) {
      params.jsonRpc = "true";
    } else if (this.isProtobufSupported()) {
      params.binaryMode = "true";
    }
    if (this.isSharedMode()) {
      if (!this._config?.clientId) {
        throw new Error("Push-server is in shared mode, but clientId is not set");
      }
      params.clientId = this._config.clientId;
    }
    if (this._session.mid) {
      params.mid = this._session.mid;
    }
    if (this._session.tag) {
      params.tag = this._session.tag;
    }
    if (this._session.time) {
      params.time = this._session.time;
    }
    params.revision = REVISION;
    return `${path}?${Text.buildQueryString(params)}`;
  }
  /**
   * @process
   */
  getPublicationPath() {
    const path = this._isSecure ? this._config?.server.publish_secure : this._config?.server.publish;
    if (!path) {
      return "";
    }
    let channels = [];
    if (this._config?.channels.private?.id) {
      channels.push(this._config.channels.private.id);
    }
    if (this._config?.channels.shared?.id) {
      channels.push(this._config.channels.shared.id);
    }
    const params = {
      CHANNEL_ID: channels.join("/")
    };
    return path + "?" + Text.buildQueryString(params);
  }
  // endregion ////
  // region Is* ////
  /**
   * @done
   */
  isConnected() {
    return this.connector ? this.connector.connected : false;
  }
  /**
   * @done
   */
  isWebSocketSupported() {
    return typeof window.WebSocket !== "undefined";
  }
  /**
   * @done
   */
  isWebSocketAllowed() {
    if (this._sharedConfig.isWebSocketBlocked()) {
      return false;
    }
    return this.isWebSocketEnabled();
  }
  /**
   * @done
   */
  isWebSocketEnabled() {
    if (!this.isWebSocketSupported()) {
      return false;
    }
    if (!this._config) {
      return false;
    }
    if (!this._config.server) {
      return false;
    }
    return this._config.server.websocket_enabled;
  }
  /**
   * @done
   */
  isPublishingSupported() {
    return this.getServerVersion() > 3;
  }
  /**
   * @done
   */
  isPublishingEnabled() {
    if (!this.isPublishingSupported()) {
      return false;
    }
    return this._config?.server.publish_enabled === true;
  }
  /**
   * @done
   */
  isProtobufSupported() {
    return this.getServerVersion() == 4 && !Browser.isIE();
  }
  /**
   * @done
   */
  isJsonRpc() {
    return this.getServerVersion() >= 5;
  }
  /**
   * @done
   */
  isSharedMode() {
    return this.getServerMode() === ServerMode.Shared;
  }
  // endregion ////
  // region Events ////
  /**
   * @dones
   * @param {TypePullClientEmitConfig} params
   * @returns {boolean}
   */
  emit(params) {
    if (params.type == SubscriptionType.Server || params.type == SubscriptionType.Client) {
      if (typeof this._subscribers[params.type] === "undefined") {
        this._subscribers[params.type] = {};
      }
      if (typeof params.moduleId === "undefined") {
        throw new Error(`${Text.getDateForLog()}: Pull.emit: parameter moduleId is not specified`);
      }
      if (typeof this._subscribers[params.type][params.moduleId] === "undefined") {
        this._subscribers[params.type][params.moduleId] = {
          callbacks: [],
          commands: {}
        };
      }
      if (this._subscribers[params.type][params.moduleId]["callbacks"].length > 0) {
        this._subscribers[params.type][params.moduleId]["callbacks"].forEach((callback) => {
          callback(
            params.data,
            {
              type: params.type,
              moduleId: params.moduleId
            }
          );
        });
      }
      if (!(typeof params.data === "undefined") && !(typeof params.data["command"] === "undefined") && this._subscribers[params.type][params.moduleId]["commands"][params.data["command"]] && this._subscribers[params.type][params.moduleId]["commands"][params.data["command"]].length > 0) {
        this._subscribers[params.type][params.moduleId]["commands"][params.data["command"]].forEach((callback) => {
          if (typeof params.data === "undefined") {
            return;
          }
          callback(
            params.data["params"],
            params.data["extra"],
            params.data["command"],
            {
              type: params.type,
              moduleId: params.moduleId
            }
          );
        });
      }
      return true;
    } else {
      if (typeof this._subscribers[params.type] === "undefined") {
        this._subscribers[params.type] = [];
      }
      if (this._subscribers[params.type].length <= 0) {
        return true;
      }
      this._subscribers[params.type].forEach((callback) => {
        callback(
          params.data,
          {
            type: params.type
          }
        );
      });
      return true;
    }
  }
  /**
   * @process
   *
   * @param message
   * @private
   */
  broadcastMessage(message) {
    const moduleId = message.module_id = message.module_id.toLowerCase();
    const command = message.command;
    if (!message.extra) {
      message.extra = {};
    }
    if (message.extra.server_time_unix) {
      message.extra.server_time_ago = ((/* @__PURE__ */ new Date()).getTime() - message.extra.server_time_unix * 1e3) / 1e3 - (this._config?.server.timeShift ? this._config?.server.timeShift : 0);
      message.extra.server_time_ago = message.extra.server_time_ago > 0 ? message.extra.server_time_ago : 0;
    }
    this.logMessage(message);
    try {
      if (message.extra.sender && message.extra.sender.type === SenderType.Client) {
        this.onCustomEvent("onPullClientEvent-" + moduleId, [command, message.params, message.extra], true);
        this.onCustomEvent("onPullClientEvent", [moduleId, command, message.params, message.extra], true);
        this.emit({
          type: SubscriptionType.Client,
          moduleId,
          data: {
            command,
            params: Type.clone(message.params),
            extra: Type.clone(message.extra)
          }
        });
      } else if (moduleId === "pull") {
        this.handleInternalPullEvent(command, message);
      } else if (moduleId == "online") {
        if ((message?.extra?.server_time_ago || 0) < 240) {
          this.onCustomEvent("onPullOnlineEvent", [command, message.params, message.extra], true);
          this.emit({
            type: SubscriptionType.Online,
            data: {
              command,
              params: Type.clone(message.params),
              extra: Type.clone(message.extra)
            }
          });
        }
        if (command === "userStatusChange") {
          this.emitUserStatusChange(
            message.params.user_id,
            message.params.online
          );
        }
      } else {
        this.onCustomEvent("onPullEvent-" + moduleId, [command, message.params, message.extra], true);
        this.onCustomEvent("onPullEvent", [moduleId, command, message.params, message.extra], true);
        this.emit({
          type: SubscriptionType.Server,
          moduleId,
          data: {
            command,
            params: Type.clone(message.params),
            extra: Type.clone(message.extra)
          }
        });
      }
    } catch (event) {
      this.getLogger().warn(
        "\n========= PULL ERROR ===========\nError type: broadcastMessages execute error\nError event: ",
        event,
        "\nMessage: ",
        message,
        "\n================================\n"
      );
    }
    if (message.extra && message.extra.revision_web) {
      this.checkRevision(Text.toInteger(message.extra.revision_web));
    }
  }
  /**
   * @process
   *
   * @param messages
   * @private
   */
  broadcastMessages(messages) {
    messages.forEach((message) => this.broadcastMessage(message));
  }
  // endregion ////
  // region sendMessage ////
  /**
   * @done
   * Sends batch of messages to the multiple public channels.
   *
   * @param messageBatchList Array of messages to send.
   * @return void
   */
  async sendMessageBatch(messageBatchList) {
    if (!this.isPublishingEnabled()) {
      this.getLogger().error(`Client publishing is not supported or is disabled`);
      return Promise.reject(new Error(`Client publishing is not supported or is disabled`));
    }
    if (this.isJsonRpc()) {
      let rpcRequest = this._jsonRpcAdapter?.createPublishRequest(messageBatchList);
      this.connector?.send(JSON.stringify(rpcRequest));
      return Promise.resolve(true);
    } else {
      let userIds = {};
      for (let i = 0; i < messageBatchList.length; i++) {
        const messageBatch = messageBatchList[i];
        if (typeof messageBatch.userList !== "undefined") {
          const cnt = messageBatch.userList.length;
          for (let j = 0; j < cnt; j++) {
            const userId = Number(messageBatch.userList[j]);
            userIds[userId] = userId;
          }
        }
      }
      this._channelManager?.getPublicIds(Object.values(userIds)).then((publicIds) => {
        const response = this.connector?.send(
          this.encodeMessageBatch(
            messageBatchList,
            publicIds
          )
        );
        return Promise.resolve(response);
      });
    }
  }
  /**
   * @done
   * @param messageBatchList
   * @param publicIds
   */
  encodeMessageBatch(messageBatchList, publicIds) {
    let messages = [];
    messageBatchList.forEach((messageFields) => {
      const messageBody = messageFields.body;
      let receivers = [];
      if (messageFields.userList) {
        receivers = this.createMessageReceivers(
          messageFields.userList,
          publicIds
        );
      }
      if (messageFields.channelList) {
        if (!Type.isArray(messageFields.channelList)) {
          throw new Error("messageFields.publicChannels must be an array");
        }
        messageFields.channelList.forEach((publicChannel) => {
          let publicId;
          let signature;
          if (typeof publicChannel === "string" && publicChannel.includes(".")) {
            const fields = publicChannel.toString().split(".");
            publicId = fields[0];
            signature = fields[1];
          } else if (typeof publicChannel === "object" && "publicId" in publicChannel && "signature" in publicChannel) {
            publicId = publicChannel?.publicId;
            signature = publicChannel?.signature;
          } else {
            throw new Error(`Public channel MUST be either a string, formatted like "{publicId}.{signature}" or an object with fields 'publicId' and 'signature'`);
          }
          receivers.push(Receiver.create({
            id: this.encodeId(publicId),
            signature: this.encodeId(signature)
          }));
        });
      }
      const message = IncomingMessage.create({
        receivers,
        body: JSON.stringify(messageBody),
        expiry: messageFields.expiry || 0
      });
      messages.push(message);
    });
    const requestBatch = RequestBatch.create({
      requests: [{
        incomingMessages: {
          messages
        }
      }]
    });
    return RequestBatch.encode(requestBatch).finish();
  }
  /**
   * @done
   * @memo fix return type
   * @param users
   * @param publicIds
   */
  createMessageReceivers(users, publicIds) {
    let result = [];
    for (let i = 0; i < users.length; i++) {
      let userId = users[i];
      if (!publicIds[userId] || !publicIds[userId].publicId) {
        throw new Error(`Could not determine public id for user ${userId}`);
      }
      result.push(Receiver.create({
        id: this.encodeId(publicIds[userId].publicId),
        signature: this.encodeId(publicIds[userId].signature)
      }));
    }
    return result;
  }
  // endregion ////
  // region _userStatusCallbacks ////
  /**
   * @done
   * @param userId
   * @param isOnline
   * @private
   */
  emitUserStatusChange(userId, isOnline) {
    if (this._userStatusCallbacks[userId]) {
      this._userStatusCallbacks[userId].forEach((callback) => callback({
        userId,
        isOnline
      }));
    }
  }
  /**
   * @done
   * @private
   */
  restoreUserStatusSubscription() {
    for (const userId in this._userStatusCallbacks) {
      if (this._userStatusCallbacks.hasOwnProperty(userId) && this._userStatusCallbacks[userId].length > 0) {
        this._jsonRpcAdapter?.executeOutgoingRpcCommand(
          RpcMethod.SubscribeStatusChange,
          {
            userId
          }
        );
      }
    }
  }
  // endregion ////
  // region Config ////
  /**
   * @done
   *
   * @param logTag
   * @private
   */
  async loadConfig(logTag) {
    if (!this._config) {
      this._config = Object.assign({}, EmptyConfig);
      let config;
      if (this._storage) {
        config = this._storage.get(LsKeys.PullConfig, null);
      }
      if (this.isConfigActual(config) && this.checkRevision(config.api.revision_web)) {
        return Promise.resolve(config);
      } else if (this._storage) {
        this._storage.remove(LsKeys.PullConfig);
      }
    } else if (this.isConfigActual(this._config) && this.checkRevision(this._config.api.revision_web)) {
      return Promise.resolve(this._config);
    } else {
      this._config = Object.assign({}, EmptyConfig);
    }
    return new Promise((resolve, reject) => {
      this._restClient.getHttpClient().setLogTag(logTag);
      this._restClient.callMethod(
        this._configGetMethod,
        {
          CACHE: "N"
        }
      ).then((response) => {
        const data = response.getData().result;
        let timeShift;
        timeShift = Math.floor(((/* @__PURE__ */ new Date()).getTime() - new Date(data.serverTime).getTime()) / 1e3);
        delete data.serverTime;
        let config = Object.assign({}, data);
        config.server.timeShift = timeShift;
        resolve(config);
      }).catch((error) => {
        if (error?.answerError?.error === "AUTHORIZE_ERROR" || error?.answerError?.error === "WRONG_AUTH_TYPE") {
          error.status = 403;
        }
        reject(error);
      }).finally(() => {
        this._restClient.getHttpClient().clearLogTag();
      });
    });
  }
  /**
   * @done
   * @param config
   */
  isConfigActual(config) {
    if (!Type.isPlainObject(config)) {
      return false;
    }
    if (Number(config.server.config_timestamp) !== this._configTimestamp) {
      return false;
    }
    const now = /* @__PURE__ */ new Date();
    if (Type.isNumber(config.exp) && config.exp > 0 && config.exp < now.getTime() / 1e3) {
      return false;
    }
    const channelCount = Object.keys(config.channels).length;
    if (channelCount === 0) {
      return false;
    }
    for (let channelType in config.channels) {
      if (!config.channels.hasOwnProperty(channelType)) {
        continue;
      }
      const channel = config.channels[channelType];
      const channelEnd = new Date(channel.end);
      if (channelEnd < now) {
        return false;
      }
    }
    return true;
  }
  /**
   * @done
   * @private
   */
  startCheckConfig() {
    if (this._checkInterval) {
      clearInterval(this._checkInterval);
      this._checkInterval = null;
    }
    this._checkInterval = setInterval(
      this.checkConfig.bind(this),
      CONFIG_CHECK_INTERVAL
    );
  }
  /**
   * @done
   */
  stopCheckConfig() {
    if (this._checkInterval) {
      clearInterval(this._checkInterval);
    }
    this._checkInterval = null;
  }
  /**
   * @done
   * @private
   */
  checkConfig() {
    if (this.isConfigActual(this._config)) {
      if (!this.checkRevision(Text.toInteger(this._config?.api.revision_web))) {
        return false;
      }
    } else {
      this.logToConsole("Stale config detected. Restarting");
      this.restart(
        CloseReasons.CONFIG_EXPIRED,
        "config expired"
      );
    }
    return true;
  }
  /**
   * @done
   *
   * @param config
   * @param allowCaching
   * @private
   */
  setConfig(config, allowCaching) {
    for (let key in config) {
      if (config.hasOwnProperty(key) && this._config?.hasOwnProperty(key)) {
        this._config[key] = config[key];
      }
    }
    if (config.publicChannels) {
      this.setPublicIds(
        Array.from(Object.values(config.publicChannels))
      );
    }
    this._configTimestamp = Number(config.server.config_timestamp);
    if (this._storage && allowCaching) {
      try {
        this._storage.set(
          LsKeys.PullConfig,
          config
        );
      } catch (error) {
        if (localStorage && localStorage.removeItem) {
          localStorage.removeItem("history");
        }
        this.getLogger().error(`${Text.getDateForLog()}: Pull: Could not cache config in local storage. Error: `, error);
      }
    }
  }
  /**
   * @done
   */
  setPublicIds(publicIds) {
    this._channelManager.setPublicIds(publicIds);
  }
  /**
   * @done
   * @param serverRevision
   * @private
   */
  checkRevision(serverRevision) {
    if (this._skipCheckRevision) {
      return true;
    }
    if (serverRevision > 0 && serverRevision !== REVISION) {
      this._enabled = false;
      this.showNotification("PULL_OLD_REVISION");
      this.disconnect(
        CloseReasons.NORMAL_CLOSURE,
        "check_revision"
      );
      this.onCustomEvent("onPullRevisionUp", [serverRevision, REVISION]);
      this.emit({
        type: SubscriptionType.Revision,
        data: {
          server: serverRevision,
          client: REVISION
        }
      });
      this.logToConsole(`Pull revision changed from ${REVISION} to ${serverRevision}. Reload required`);
      return false;
    }
    return true;
  }
  // endregion ////
  // region Connect|ReConnect|DisConnect ////
  /**
   * @done
   */
  disconnect(disconnectCode, disconnectReason) {
    if (this.connector) {
      this._isManualDisconnect = true;
      this.connector.disconnect(
        disconnectCode,
        disconnectReason
      );
    }
  }
  /**
   * @done
   */
  restoreWebSocketConnection() {
    if (this._connectionType === ConnectionType.WebSocket) {
      return;
    }
    this._connectors.webSocket?.connect();
  }
  /**
   * @done
   * @param connectionDelay
   * @private
   */
  scheduleReconnect(connectionDelay = 0) {
    if (!this._enabled) {
      return;
    }
    if (!connectionDelay) {
      {
        connectionDelay = this.getConnectionAttemptDelay(this._connectionAttempt);
      }
    }
    if (this._reconnectTimeout) {
      clearTimeout(this._reconnectTimeout);
      this._reconnectTimeout = null;
    }
    this.logToConsole(
      `Pull: scheduling reconnection in ${connectionDelay} seconds; attempt # ${this._connectionAttempt}`
    );
    this._reconnectTimeout = setTimeout(
      () => {
        this.connect().catch((error) => {
          this.getLogger().error(error);
        });
      },
      connectionDelay * 1e3
    );
  }
  /**
   * @done
   * @private
   */
  scheduleRestoreWebSocketConnection() {
    this.logToConsole(
      `Pull: scheduling restoration of websocket connection in ${RESTORE_WEBSOCKET_TIMEOUT} seconds`
    );
    if (this._restoreWebSocketTimeout) {
      return;
    }
    this._restoreWebSocketTimeout = setTimeout(
      () => {
        this._restoreWebSocketTimeout = 0;
        this.restoreWebSocketConnection();
      },
      RESTORE_WEBSOCKET_TIMEOUT * 1e3
    );
  }
  /**
   * @done
   * @returns {Promise}
   */
  async connect() {
    if (!this._enabled) {
      return Promise.reject();
    }
    if (this.connector?.connected) {
      return Promise.resolve();
    }
    if (this._reconnectTimeout) {
      clearTimeout(this._reconnectTimeout);
      this._reconnectTimeout = null;
    }
    this.status = PullStatus.Connecting;
    this._connectionAttempt++;
    return new Promise((resolve, reject) => {
      this._connectPromise = {
        resolve,
        reject
      };
      this.connector?.connect();
    });
  }
  /**
   * @done
   * @param disconnectCode
   * @param disconnectReason
   * @param restartDelay
   * @private
   */
  scheduleRestart(disconnectCode, disconnectReason, restartDelay = 0) {
    if (this._restartTimeout) {
      clearTimeout(this._restartTimeout);
      this._restartTimeout = null;
    }
    if (restartDelay < 1) {
      restartDelay = Math.ceil(Math.random() * 30) + 5;
    }
    this._restartTimeout = setTimeout(
      () => this.restart(disconnectCode, disconnectReason),
      restartDelay * 1e3
    );
  }
  // endregion ////
  // region Handlers ////
  /**
   * @done
   *
   * @param messageFields
   * @private
   */
  handleRpcIncomingMessage(messageFields) {
    this._session.mid = messageFields.mid;
    let body = messageFields.body;
    if (!messageFields.body.extra) {
      body.extra = {};
    }
    body.extra.sender = messageFields.sender;
    if ("user_params" in messageFields && Type.isPlainObject(messageFields.user_params)) {
      Object.assign(body.params, messageFields.user_params);
    }
    if ("dictionary" in messageFields && Type.isPlainObject(messageFields.dictionary)) {
      Object.assign(body.params, messageFields.dictionary);
    }
    if (this.checkDuplicate(messageFields.mid)) {
      this.addMessageToStat(body);
      this.trimDuplicates();
      this.broadcastMessage(body);
    }
    this.connector?.send(`mack:${messageFields.mid}`);
    return {};
  }
  /**
   * @done
   * @param events
   * @private
   */
  handleIncomingEvents(events) {
    let messages = [];
    if (events.length === 0) {
      this._session.mid = null;
      return;
    }
    for (let i = 0; i < events.length; i++) {
      let event = events[i];
      this.updateSessionFromEvent(event);
      if (event.mid && !this.checkDuplicate(event.mid)) {
        continue;
      }
      this.addMessageToStat(
        event.text
      );
      messages.push(event.text);
    }
    this.trimDuplicates();
    this.broadcastMessages(messages);
  }
  /**
   * @done
   * @param event
   * @private
   */
  updateSessionFromEvent(event) {
    this._session.mid = event.mid || null;
    this._session.tag = event.tag || null;
    this._session.time = event.time || null;
  }
  /**
   * @process
   *
   * @param command
   * @param message
   * @private
   */
  handleInternalPullEvent(command, message) {
    switch (command.toUpperCase()) {
      case SystemCommands.CHANNEL_EXPIRE: {
        if (message.params.action === "reconnect") {
          const typeChanel = message.params?.channel.type;
          if (typeChanel === "private" && this._config?.channels?.private) {
            this._config.channels.private = message.params.new_channel;
            this.logToConsole(`Pull: new config for ${message.params.channel.type} channel set: ${this._config.channels.private}`);
          }
          if (typeChanel === "shared" && this._config?.channels?.shared) {
            this._config.channels.shared = message.params.new_channel;
            this.logToConsole(`Pull: new config for ${message.params.channel.type} channel set: ${this._config.channels.shared}`);
          }
          this.reconnect(
            CloseReasons.CONFIG_REPLACED,
            "config was replaced"
          );
        } else {
          this.restart(
            CloseReasons.CHANNEL_EXPIRED,
            "channel expired received"
          );
        }
        break;
      }
      case SystemCommands.CONFIG_EXPIRE: {
        this.restart(
          CloseReasons.CONFIG_EXPIRED,
          "config expired received"
        );
        break;
      }
      case SystemCommands.SERVER_RESTART: {
        this.reconnect(
          CloseReasons.SERVER_RESTARTED,
          "server was restarted",
          15
        );
        break;
      }
    }
  }
  // region Handlers For Message ////
  /**
   * @done
   * @param response
   * @private
   */
  onIncomingMessage(response) {
    if (this.isJsonRpc()) {
      response === JSON_RPC_PING ? this.onJsonRpcPing() : this._jsonRpcAdapter?.parseJsonRpcMessage(
        response
      );
    } else {
      const events = this.extractMessages(response);
      this.handleIncomingEvents(events);
    }
  }
  // region onLongPolling ////
  /**
   * @done
   */
  onLongPollingOpen() {
    this._unloading = false;
    this._starting = false;
    this._connectionAttempt = 0;
    this._isManualDisconnect = false;
    this.status = PullStatus.Online;
    this.logToConsole("Pull: Long polling connection with push-server opened");
    if (this.isWebSocketEnabled()) {
      this.scheduleRestoreWebSocketConnection();
    }
    if (this._connectPromise) {
      this._connectPromise.resolve({});
    }
  }
  /**
   * @done
   * @param response
   * @private
   */
  onLongPollingDisconnect(response) {
    if (this._connectionType === ConnectionType.LongPolling) {
      this.status = PullStatus.Offline;
    }
    this.logToConsole(`Pull: Long polling connection with push-server closed. Code: ${response.code}, reason: ${response.reason}`);
    if (!this._isManualDisconnect) {
      this.scheduleReconnect();
    }
    this._isManualDisconnect = false;
    this.clearPingWaitTimeout();
  }
  /**
   * @done
   * @param error
   */
  onLongPollingError(error) {
    this._starting = false;
    if (this._connectionType === ConnectionType.LongPolling) {
      this.status = PullStatus.Offline;
    }
    this.getLogger().error(`${Text.getDateForLog()}: Pull: Long polling connection error `, error);
    this.scheduleReconnect();
    if (this._connectPromise) {
      this._connectPromise.reject(error);
    }
    this.clearPingWaitTimeout();
  }
  // endregion ////
  // region onWebSocket ////
  /**
   * @done
   * @param response
   * @private
   */
  onWebSocketBlockChanged(response) {
    const isWebSocketBlocked = response.isWebSocketBlocked;
    if (isWebSocketBlocked && this._connectionType === ConnectionType.WebSocket && !this.isConnected()) {
      if (this._reconnectTimeout) {
        clearTimeout(this._reconnectTimeout);
        this._reconnectTimeout = null;
      }
      this._connectionAttempt = 0;
      this._connectionType = ConnectionType.LongPolling;
      this.scheduleReconnect(1);
    } else if (!isWebSocketBlocked && this._connectionType === ConnectionType.LongPolling) {
      if (this._reconnectTimeout) {
        clearTimeout(this._reconnectTimeout);
        this._reconnectTimeout = null;
      }
      if (this._restoreWebSocketTimeout) {
        clearTimeout(this._restoreWebSocketTimeout);
        this._restoreWebSocketTimeout = null;
      }
      this._connectionAttempt = 0;
      this._connectionType = ConnectionType.WebSocket;
      this.scheduleReconnect(1);
    }
  }
  /**
   * @done
   */
  onWebSocketOpen() {
    this._unloading = false;
    this._starting = false;
    this._connectionAttempt = 0;
    this._isManualDisconnect = false;
    this.status = PullStatus.Online;
    this._sharedConfig.setWebSocketBlocked(false);
    this._sharedConfig.setLongPollingBlocked(true);
    if (this._connectionType == ConnectionType.LongPolling) {
      this._connectionType = ConnectionType.WebSocket;
      this._connectors.longPolling?.disconnect(
        CloseReasons.CONFIG_REPLACED,
        "Fire at onWebSocketOpen"
      );
    }
    if (this._restoreWebSocketTimeout) {
      clearTimeout(this._restoreWebSocketTimeout);
      this._restoreWebSocketTimeout = null;
    }
    this.logToConsole("Pull: Websocket connection with push-server opened");
    if (this._connectPromise) {
      this._connectPromise.resolve({});
    }
    this.restoreUserStatusSubscription();
  }
  /**
   * @done
   * @param response
   * @private
   */
  onWebSocketDisconnect(response) {
    if (this._connectionType === ConnectionType.WebSocket) {
      this.status = PullStatus.Offline;
    }
    this.logToConsole(`Pull: Websocket connection with push-server closed. Code: ${response.code}, reason: ${response.reason}`, true);
    if (!this._isManualDisconnect) {
      if (response.code == CloseReasons.WRONG_CHANNEL_ID) {
        this.scheduleRestart(
          CloseReasons.WRONG_CHANNEL_ID,
          "wrong channel signature"
        );
      } else {
        this.scheduleReconnect();
      }
    }
    this._sharedConfig.setLongPollingBlocked(true);
    this._isManualDisconnect = false;
    this.clearPingWaitTimeout();
  }
  /**
   * @done
   * @param error
   */
  onWebSocketError(error) {
    this._starting = false;
    if (this._connectionType === ConnectionType.WebSocket) {
      this.status = PullStatus.Offline;
    }
    this.getLogger().error(`${Text.getDateForLog()}: Pull: WebSocket connection error `, error);
    this.scheduleReconnect();
    if (this._connectPromise) {
      this._connectPromise.reject(error);
    }
    this.clearPingWaitTimeout();
  }
  // endregion ////
  // endregion ////
  // endregion ////
  // region extractMessages ////
  /**
   * @done
   * @param pullEvent
   * @private
   */
  extractMessages(pullEvent) {
    if (pullEvent instanceof ArrayBuffer) {
      return this.extractProtobufMessages(pullEvent);
    } else if (Type.isStringFilled(pullEvent)) {
      return this.extractPlainTextMessages(pullEvent);
    }
    throw new Error("Error pullEvent type");
  }
  /**
   * @done
   * @param pullEvent
   * @private
   */
  extractProtobufMessages(pullEvent) {
    let result = [];
    try {
      let responseBatch = ResponseBatch.decode(new Uint8Array(pullEvent));
      for (let i = 0; i < responseBatch.responses.length; i++) {
        let response = responseBatch.responses[i];
        if (response.command !== "outgoingMessages") {
          continue;
        }
        let messages = response.outgoingMessages.messages;
        for (let m = 0; m < messages.length; m++) {
          const message = messages[m];
          let messageFields;
          try {
            messageFields = JSON.parse(message.body);
          } catch (error) {
            this.getLogger().error(`${Text.getDateForLog()}: Pull: Could not parse message body `, error);
            continue;
          }
          if (!messageFields.extra) {
            messageFields.extra = {};
          }
          messageFields.extra.sender = {
            type: message.sender.type
          };
          if (message.sender.id instanceof Uint8Array) {
            messageFields.extra.sender.id = this.decodeId(message.sender.id);
          }
          const compatibleMessage = {
            mid: this.decodeId(message.id),
            text: messageFields
          };
          result.push(compatibleMessage);
        }
      }
    } catch (error) {
      this.getLogger().error(`${Text.getDateForLog()}: Pull: Could not parse message `, error);
    }
    return result;
  }
  /**
   * @done
   * @param pullEvent
   * @private
   */
  extractPlainTextMessages(pullEvent) {
    let result = [];
    const dataArray = pullEvent.match(/#!NGINXNMS!#(.*?)#!NGINXNME!#/gm);
    if (dataArray === null) {
      const text = `
========= PULL ERROR ===========
Error type: parseResponse error parsing message

Data string: ${pullEvent}
================================

`;
      this.getLogger().warn(text);
      return [];
    }
    for (let i = 0; i < dataArray.length; i++) {
      dataArray[i] = dataArray[i].substring(12, dataArray[i].length - 12);
      if (dataArray[i].length <= 0) {
        continue;
      }
      let data;
      try {
        data = JSON.parse(dataArray[i]);
      } catch (error) {
        continue;
      }
      result.push(data);
    }
    return result;
  }
  /**
   * @done
   * Converts message id from byte[] to string
   * @param {Uint8Array} encodedId
   * @return {string}
   */
  decodeId(encodedId) {
    let result = "";
    for (let i = 0; i < encodedId.length; i++) {
      const hexByte = encodedId[i].toString(16);
      if (hexByte.length === 1) {
        result += "0";
      }
      result += hexByte;
    }
    return result;
  }
  /**
   * @done
   * Converts message id from hex-encoded string to byte[]
   * @param {string} id Hex-encoded string.
   * @return {Uint8Array}
   */
  encodeId(id) {
    if (!id) {
      return new Uint8Array();
    }
    let result = [];
    for (let i = 0; i < id.length; i += 2) {
      result.push(parseInt(id.substring(i, 2), 16));
    }
    return new Uint8Array(result);
  }
  // endregion ////
  // region Events.Status /////
  /**
   * @done
   */
  onOffline() {
    this.disconnect(
      CloseReasons.NORMAL_CLOSURE,
      "offline"
    );
  }
  /**
   * @done
   */
  onOnline() {
    this.connect().catch((error) => {
      this.getLogger().error(error);
    });
  }
  /**
   * @done
   * @private
   */
  onBeforeUnload() {
    this._unloading = true;
    const session = Type.clone(this.session);
    session.ttl = (/* @__PURE__ */ new Date()).getTime() + LS_SESSION_CACHE_TIME * 1e3;
    if (this._storage) {
      try {
        this._storage.set(
          LS_SESSION,
          JSON.stringify(session)
          //LS_SESSION_CACHE_TIME
        );
      } catch (error) {
        this.getLogger().error(`${Text.getDateForLog()}: Pull: Could not save session info in local storage. Error: `, error);
      }
    }
    this.scheduleReconnect(15);
  }
  // endregion ////
  // region PullStatus ////
  /**
   * @done
   * @param status
   * @param delay
   * @private
   */
  sendPullStatusDelayed(status, delay) {
    if (this._offlineTimeout) {
      clearTimeout(this._offlineTimeout);
      this._offlineTimeout = null;
    }
    this._offlineTimeout = setTimeout(
      () => {
        this._offlineTimeout = null;
        this.sendPullStatus(status);
      },
      delay
    );
  }
  /**
   * @done
   * @param status
   * @private
   */
  sendPullStatus(status) {
    if (this._unloading) {
      return;
    }
    this.onCustomEvent("onPullStatus", [status]);
    this.emit({
      type: SubscriptionType.Status,
      data: {
        status
      }
    });
  }
  // endregion ////
  // region _watchTagsQueue ////
  /**
   * @done
   * @memo if private ?
   * @param tagId
   * @param force
   */
  extendWatch(tagId, force = false) {
    if (this._watchTagsQueue.get(tagId)) {
      return;
    }
    this._watchTagsQueue.set(tagId, true);
    if (force) {
      this.updateWatch(force);
    }
  }
  /**
   * @done
   * @param force
   * @private
   */
  updateWatch(force = false) {
    if (this._watchUpdateTimeout) {
      clearTimeout(this._watchUpdateTimeout);
      this._watchUpdateTimeout = null;
    }
    this._watchUpdateTimeout = setTimeout(
      () => {
        const watchTags = Array.from(this._watchTagsQueue.keys());
        if (watchTags.length > 0) {
          this._restClient.callMethod(
            "pull.watch.extend",
            {
              tags: watchTags
            }
          ).then((response) => {
            const updatedTags = response.getData().result;
            updatedTags.forEach((tagId) => this.clearWatch(tagId));
            this.updateWatch();
          }).catch(() => {
            this.updateWatch();
          });
        } else {
          this.updateWatch();
        }
      },
      force ? this._watchForceUpdateInterval : this._watchUpdateInterval
    );
  }
  /**
   * @done
   * @param tagId
   * @private
   */
  clearWatch(tagId) {
    this._watchTagsQueue.delete(tagId);
  }
  // endregion ////
  // region Ping ////
  /**
   * @done
   * @private
   */
  onJsonRpcPing() {
    this.updatePingWaitTimeout();
    this.connector?.send(
      JSON_RPC_PONG
    );
  }
  /**
   * @done
   * @private
   */
  updatePingWaitTimeout() {
    if (this._pingWaitTimeout) {
      clearTimeout(this._pingWaitTimeout);
      this._pingWaitTimeout = null;
    }
    this._pingWaitTimeout = setTimeout(
      this._onPingTimeoutHandler,
      PING_TIMEOUT * 2 * 1e3
    );
  }
  /**
   * @done
   * @private
   */
  clearPingWaitTimeout() {
    if (this._pingWaitTimeout) {
      clearTimeout(this._pingWaitTimeout);
    }
    this._pingWaitTimeout = null;
  }
  /**
   * @done
   * @private
   */
  onPingTimeout() {
    this._pingWaitTimeout = null;
    if (!this._enabled || !this.isConnected()) {
      return;
    }
    this.getLogger().warn(`No pings are received in ${PING_TIMEOUT * 2} seconds. Reconnecting`);
    this.disconnect(
      CloseReasons.STUCK,
      "connection stuck"
    );
    this.scheduleReconnect();
  }
  // endregion ////
  // region Time ////
  /**
   * @done
   * Returns reconnect delay in seconds
   *
   * @param attemptNumber
   * @return {number}
   */
  getConnectionAttemptDelay(attemptNumber) {
    let result;
    if (attemptNumber < 1) {
      result = 0.5;
    } else if (attemptNumber < 3) {
      result = 15;
    } else if (attemptNumber < 5) {
      result = 45;
    } else if (attemptNumber < 10) {
      result = 600;
    } else {
      result = 3600;
    }
    return result + result * Math.random() * 0.2;
  }
  // endregion ////
  // region Tools ////
  /**
   * @done
   * @param mid
   */
  checkDuplicate(mid) {
    if (this._session.lastMessageIds.includes(mid)) {
      this.getLogger().warn(`Duplicate message ${mid} skipped`);
      return false;
    } else {
      this._session.lastMessageIds.push(mid);
      return true;
    }
  }
  /**
   * @done
   */
  trimDuplicates() {
    if (this._session.lastMessageIds.length > MAX_IDS_TO_STORE) {
      this._session.lastMessageIds = this._session.lastMessageIds.slice(-MAX_IDS_TO_STORE);
    }
  }
  // endregion ////
  // region Logging ////
  /**
   * @done
   * @param message
   * @private
   */
  logMessage(message) {
    if (!this._debug) {
      return;
    }
    if (message.extra?.sender && message.extra.sender.type === SenderType.Client) {
      this.getLogger().info(`onPullClientEvent-${message.module_id}`, message.command, message.params, message.extra);
    } else if (message.module_id == "online") {
      this.getLogger().info(`onPullOnlineEvent`, message.command, message.params, message.extra);
    } else {
      this.getLogger().info(`onPullEvent`, message.module_id, message.command, message.params, message.extra);
    }
  }
  /**
   * @done
   * @param message
   * @param force
   * @private
   */
  logToConsole(message, force = false) {
    if (this._loggingEnabled || force) {
      this.getLogger().log(`${Text.getDateForLog()}: ${message}`);
    }
  }
  /**
   * @done
   * @param message
   * @private
   */
  addMessageToStat(message) {
    if (!this._session.history[message.module_id]) {
      this._session.history[message.module_id] = {};
    }
    if (!this._session.history[message.module_id][message.command]) {
      this.session.history[message.module_id][message.command] = 0;
    }
    this._session.history[message.module_id][message.command]++;
    this._session.messageCount++;
  }
  /**
   * @done
   *
   * @param text
   */
  showNotification(text) {
    this.getLogger().warn(text);
  }
  // endregion ////
  // region onCustomEvent ////
  /**
   * @done
   * @memo may be need use onCustomEvent
   * @memo wtf ? force
   */
  onCustomEvent(eventName, data, force = false) {
  }
  // endregion ////
  // region deprecated /////
  /**
   * @deprecated
   */
  /*/
  	getRestClientOptions()
  	{
  		let result = {};
  
  		if (this.guestMode && this.guestUserId !== 0)
  		{
  			result.queryParams = {
  				pull_guest_id: this.guestUserId
  			}
  		}
  		return result;
  	}
  	//*/
  // endregion ////
}

class B24HelperManager {
  _b24;
  _logger = null;
  _isInit = false;
  _profile = null;
  _app = null;
  _payment = null;
  _license = null;
  _currency = null;
  _appOptions = null;
  _userOptions = null;
  _b24PullClient = null;
  _pullClientUnSubscribe = [];
  _pullClientModuleId = "";
  constructor(b24) {
    this._b24 = b24;
    this.setLogger(this._b24.getLogger());
  }
  setLogger(logger) {
    this._logger = logger;
    if (null !== this._profile) {
      this._profile.setLogger(this.getLogger());
    }
    if (null !== this._app) {
      this._app.setLogger(this.getLogger());
    }
    if (null !== this._payment) {
      this._payment.setLogger(this.getLogger());
    }
    if (null !== this._license) {
      this._license.setLogger(this.getLogger());
    }
    if (null !== this._currency) {
      this._currency.setLogger(this.getLogger());
    }
    if (null !== this._appOptions) {
      this._appOptions.setLogger(this.getLogger());
    }
    if (null !== this._userOptions) {
      this._userOptions.setLogger(this.getLogger());
    }
  }
  getLogger() {
    if (null === this._logger) {
      this._logger = LoggerBrowser.build(`NullLogger`);
      this._logger.setConfig({
        [LoggerType.desktop]: false,
        [LoggerType.log]: false,
        [LoggerType.info]: false,
        [LoggerType.warn]: false,
        [LoggerType.error]: true,
        [LoggerType.trace]: false
      });
    }
    return this._logger;
  }
  destroy() {
    this._destroyPullClient();
  }
  // region loadData ////
  async loadData(dataTypes = [
    LoadDataType.App,
    LoadDataType.Profile
  ]) {
    const batchMethods = {
      [LoadDataType.App]: { method: "app.info" },
      [LoadDataType.Profile]: { method: "profile" },
      [LoadDataType.Currency]: [{ method: "crm.currency.base.get" }, { method: "crm.currency.list" }],
      [LoadDataType.AppOptions]: { method: "app.option.get" },
      [LoadDataType.UserOptions]: { method: "user.option.get" }
    };
    const batchRequest = dataTypes.reduce((acc, type) => {
      if (batchMethods[type]) {
        if (Array.isArray(batchMethods[type])) {
          batchMethods[type].forEach((row, index) => {
            acc[`get_${type}_${index}`] = row;
          });
        } else {
          acc[`get_${type}`] = batchMethods[type];
        }
      }
      return acc;
    }, {});
    try {
      const response = await this._b24.callBatch(batchRequest);
      const data = response.getData();
      if (data[`get_${LoadDataType.App}`]) {
        this._app = await this.parseAppData(data[`get_${LoadDataType.App}`]);
        this._payment = await this.parsePaymentData(data[`get_${LoadDataType.App}`]);
        this._license = await this.parseLicenseData(data[`get_${LoadDataType.App}`]);
      }
      if (data[`get_${LoadDataType.Profile}`]) {
        this._profile = await this.parseUserData(data[`get_${LoadDataType.Profile}`]);
      }
      if (data[`get_${LoadDataType.Currency}_0`] && data[`get_${LoadDataType.Currency}_1`]) {
        this._currency = await this.parseCurrencyData({
          currencyBase: data[`get_${LoadDataType.Currency}_0`],
          currencyList: data[`get_${LoadDataType.Currency}_1`]
        });
      }
      if (data[`get_${LoadDataType.AppOptions}`]) {
        this._appOptions = await this.parseOptionsData("app", data[`get_${LoadDataType.AppOptions}`]);
      }
      if (data[`get_${LoadDataType.UserOptions}`]) {
        this._userOptions = await this.parseOptionsData("user", data[`get_${LoadDataType.UserOptions}`]);
      }
      this._isInit = true;
    } catch (error) {
      console.error("Error loading data:", error);
      throw new Error("Failed to load data");
    }
  }
  async parseUserData(profileData) {
    const manager = new ProfileManager(this._b24);
    manager.setLogger(this.getLogger());
    return manager.initData({
      id: Number(profileData.ID),
      isAdmin: profileData.ADMIN === true,
      lastName: profileData?.LAST_NAME || "",
      name: profileData?.NAME || "",
      gender: profileData?.PERSONAL_GENDER || "",
      photo: profileData?.PERSONAL_PHOTO || "",
      TimeZone: profileData?.TIME_ZONE || "",
      TimeZoneOffset: profileData?.TIME_ZONE_OFFSET
    }).then(() => {
      return manager;
    });
  }
  async parseAppData(appData) {
    const manager = new AppManager(this._b24);
    manager.setLogger(this.getLogger());
    return manager.initData({
      id: parseInt(appData.ID),
      code: appData.CODE,
      version: parseInt(appData.VERSION),
      status: appData.STATUS,
      isInstalled: appData.INSTALLED
    }).then(() => {
      return manager;
    });
  }
  async parsePaymentData(appData) {
    const manager = new PaymentManager(this._b24);
    manager.setLogger(this.getLogger());
    return manager.initData({
      isExpired: appData.PAYMENT_EXPIRED === "Y",
      days: parseInt(appData.DAYS || "0")
    }).then(() => {
      return manager;
    });
  }
  async parseLicenseData(appData) {
    const manager = new LicenseManager(this._b24);
    manager.setLogger(this.getLogger());
    return manager.initData({
      languageId: appData.LANGUAGE_ID,
      license: appData.LICENSE,
      licensePrevious: appData.LICENSE_PREVIOUS,
      licenseType: appData.LICENSE_TYPE,
      licenseFamily: appData.LICENSE_FAMILY,
      isSelfHosted: appData.LICENSE.includes("selfhosted")
    }).then(() => {
      return manager;
    });
  }
  async parseCurrencyData(currencyData) {
    const manager = new CurrencyManager(this._b24);
    manager.setLogger(this.getLogger());
    return manager.initData(currencyData).then(() => {
      return manager;
    });
  }
  async parseOptionsData(type, optionsData) {
    const manager = new OptionsManager(this._b24, type);
    manager.setLogger(this.getLogger());
    return manager.initData(optionsData).then(() => {
      return manager;
    });
  }
  // endregion ////
  // region Get ////
  get isInit() {
    return this._isInit;
  }
  get forB24Form() {
    this.ensureInitialized();
    if (null === this._profile) {
      throw new Error("B24HelperManager.profileInfo not initialized");
    }
    if (null === this._app) {
      throw new Error("B24HelperManager.appInfo not initialized");
    }
    return {
      app_code: this.appInfo.data.code,
      app_status: this.appInfo.data.status,
      payment_expired: this.paymentInfo.data.isExpired ? "Y" : "N",
      days: this.paymentInfo.data.days,
      b24_plan: this.licenseInfo.data.license,
      c_name: this.profileInfo.data.name,
      c_last_name: this.profileInfo.data.lastName,
      hostname: this.hostName
    };
  }
  /**
   * Get the account address BX24 ( https://name.bitrix24.com )
   */
  get hostName() {
    return this._b24.getTargetOrigin();
  }
  get profileInfo() {
    this.ensureInitialized();
    if (null === this._profile) {
      throw new Error("B24HelperManager.profileInfo not initialized");
    }
    return this._profile;
  }
  get appInfo() {
    this.ensureInitialized();
    if (null === this._app) {
      throw new Error("B24HelperManager.appInfo not initialized");
    }
    return this._app;
  }
  get paymentInfo() {
    this.ensureInitialized();
    if (null === this._payment) {
      throw new Error("B24HelperManager.paymentInfo not initialized");
    }
    return this._payment;
  }
  get licenseInfo() {
    this.ensureInitialized();
    if (null === this._license) {
      throw new Error("B24HelperManager.licenseInfo not initialized");
    }
    return this._license;
  }
  get currency() {
    this.ensureInitialized();
    if (null === this._currency) {
      throw new Error("B24HelperManager.currency not initialized");
    }
    return this._currency;
  }
  get appOptions() {
    this.ensureInitialized();
    if (null === this._appOptions) {
      throw new Error("B24HelperManager.appOptions not initialized");
    }
    return this._appOptions;
  }
  get userOptions() {
    this.ensureInitialized();
    if (null === this._userOptions) {
      throw new Error("B24HelperManager.userOptions not initialized");
    }
    return this._userOptions;
  }
  // endregion ////
  // region Custom SelfHosted && Cloud ////
  get isSelfHosted() {
    return this.licenseInfo.data.isSelfHosted;
  }
  /**
   * Returns the increment step of fields of type ID
   * @memo in cloud step = 2 in box step = 1
   *
   * @returns {number}
   */
  get primaryKeyIncrementValue() {
    if (this.isSelfHosted) {
      return 1;
    }
    return 2;
  }
  /**
   * Defines specific URLs for a Bitrix24 box or cloud
   */
  get b24SpecificUrl() {
    if (this.isSelfHosted) {
      return {
        [TypeSpecificUrl.MainSettings]: "/configs/",
        [TypeSpecificUrl.UfList]: "/configs/userfield_list.php",
        [TypeSpecificUrl.UfPage]: "/configs/userfield.php"
      };
    }
    return {
      [TypeSpecificUrl.MainSettings]: "/settings/configs/",
      [TypeSpecificUrl.UfList]: "/settings/configs/userfield_list.php",
      [TypeSpecificUrl.UfPage]: "/settings/configs/userfield.php"
    };
  }
  // endregion ////
  // region Pull.Client ////
  usePullClient(prefix = "prefix", userId) {
    if (this._b24PullClient) {
      return this;
    }
    this.initializePullClient(
      typeof userId === "undefined" ? this.profileInfo.data.id || 0 : userId,
      prefix
    );
    return this;
  }
  initializePullClient(userId, prefix = "prefix") {
    this._b24PullClient = new PullClient({
      b24: this._b24,
      restApplication: this._b24.auth.getUniq(prefix),
      userId
    });
  }
  subscribePullClient(callback, moduleId = "application") {
    if (!this._b24PullClient) {
      throw new Error("PullClient not init");
    }
    this._pullClientModuleId = moduleId;
    this._pullClientUnSubscribe.push(
      this._b24PullClient.subscribe({
        moduleId: this._pullClientModuleId,
        callback
      })
    );
    return this;
  }
  startPullClient() {
    if (!this._b24PullClient) {
      throw new Error("PullClient not init");
    }
    this._b24PullClient.start().catch((error) => {
      this.getLogger().error(`${Text.getDateForLog()}: Pull not running`, error);
    });
  }
  getModuleIdPullClient() {
    if (!this._b24PullClient) {
      throw new Error("PullClient not init");
    }
    return this._pullClientModuleId;
  }
  _destroyPullClient() {
    this._pullClientUnSubscribe.forEach(
      (unsubscribeCallback) => unsubscribeCallback()
    );
    this._b24PullClient?.destroy();
    this._b24PullClient = null;
  }
  // endregion ////
  // region Tools ////
  ensureInitialized() {
    if (!this._isInit) {
      throw new Error("B24HelperManager not initialized");
    }
  }
  // endregion ////
}

const useB24Helper = () => {
  let $isInitB24Helper = false;
  let $isInitPullClient = false;
  let $b24Helper = null;
  const initB24Helper = async ($b24, dataTypes = [
    LoadDataType.App,
    LoadDataType.Profile
  ]) => {
    if (null === $b24Helper) {
      $b24Helper = new B24HelperManager($b24);
    }
    if ($isInitB24Helper) {
      return $b24Helper;
    }
    return $b24Helper.loadData(dataTypes).then(() => {
      $isInitB24Helper = true;
      return $b24Helper;
    });
  };
  const destroyB24Helper = () => {
    $b24Helper?.destroy();
    $b24Helper = null;
    $isInitB24Helper = false;
    $isInitPullClient = false;
  };
  const isInitB24Helper = () => {
    return $isInitB24Helper;
  };
  const getB24Helper = () => {
    if (null === $b24Helper) {
      throw new Error("B24HelperManager is not initialized. You need to call initB24Helper first.");
    }
    return $b24Helper;
  };
  const usePullClient = () => {
    if (null === $b24Helper) {
      throw new Error("B24HelperManager is not initialized. You need to call initB24Helper first.");
    }
    $b24Helper.usePullClient();
    $isInitPullClient = true;
  };
  const useSubscribePullClient = (callback, moduleId = "application") => {
    if (!$isInitPullClient) {
      throw new Error("PullClient is not initialized. You need to call usePullClient first.");
    }
    $b24Helper?.subscribePullClient(callback, moduleId);
  };
  const startPullClient = () => {
    if (!$isInitPullClient) {
      throw new Error("PullClient is not initialized. You need to call usePullClient first.");
    }
    $b24Helper?.startPullClient();
  };
  return {
    initB24Helper,
    isInitB24Helper,
    destroyB24Helper,
    getB24Helper,
    usePullClient,
    useSubscribePullClient,
    startPullClient
  };
};

export { AbstractB24, AjaxError, AjaxResult, AppFrame, AuthHookManager, AuthManager, B24Frame, B24Hook, B24LangList, PullClient as B24PullClientManager, Browser, CloseReasons, ConnectionType, DataType, DialogManager, EnumAppStatus, EnumCrmEntityType, EnumCrmEntityTypeId, ListRpcError, LoadDataType, LoggerBrowser, LoggerType, LsKeys, MessageCommands, MessageManager, OptionsManager$1 as OptionsManager, ParentManager, PlacementManager, PullStatus, RestrictionManagerParamsBase, RestrictionManagerParamsForEnterprise, Result, RpcMethod, SenderType, ServerMode, SliderManager, StatusDescriptions, SubscriptionType, SystemCommands, Text, Type, TypeOption, TypeSpecificUrl, useB24Helper, useFormatter };
