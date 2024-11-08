'use strict';

class msCDEK {
  constructor() {
    if (window.msCDEK) return window.msCDEK;

    this.events = {
      init: 'mscdek:init',
    }

    this.config = window.mscdek_config;
    window.msCDEK = this;

    this.initialize().then(() => {
      document.dispatchEvent(new CustomEvent(this.events.init, {}));
    });
  }

  async initialize() {
    for (let k in this.config) {
      await this.importModule(this.config[k]['pathToScripts'], k);
    }
  }

  async importModule(pathToModule, property) {
    const {default: moduleName} = await import(pathToModule);
    if (property === "config") {
      this[property] = moduleName();
    } else {
      this[property] = new moduleName(this.config[property])
    }
    try {

    } catch (e) {
      throw new Error(e);
    }
  }

  async sendRequest(url, params = new FormData(), headers = {}, method = 'POST') {
    const fetchOptions = {
      method: method,
      headers: headers
    }

    if (method === 'POST') {
      fetchOptions.body = params;
    }

    const response = await fetch(url, fetchOptions);

    return await response.json();
  }

  setCookie(name, value, options = {}) {

    options = {
      path: '/',
      ...options
    };

    if (options.expires instanceof Date) {
      options.expires = options.expires.toUTCString();
    }

    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

    for (let optionKey in options) {
      updatedCookie += "; " + optionKey;
      let optionValue = options[optionKey];
      if (optionValue !== true) {
        updatedCookie += "=" + optionValue;
      }
    }

    document.cookie = updatedCookie;
  }

  loadStyles(stylePath) {
    if(!stylePath) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = stylePath
    document.head.appendChild(link);
  }

}

new msCDEK();
