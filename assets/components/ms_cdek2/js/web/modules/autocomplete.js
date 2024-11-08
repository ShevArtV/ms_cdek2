export default class AutoComplete {
  constructor(config) {
    if (window.msCDEK && window.msCDEK.AutoComplete) return window.msCDEK.AutoComplete;
    const defaults = {
      actionUrl: "https://api.cdek.ru/city/getListByTerm/jsonp.php",
      fieldSelector: "[name='city']",
      wrapperSelector: "[data-cdek-suggestions]",
      suggestionSelector: "[data-cdek-suggestion]",
      wrapperTpl: "<ul data-cdek-suggestions></ul>",
      rowTpl: "<li data-cdek-suggestion='${data}'>${title}</li>",
      dataAttr: "cdekSuggestion",
      minQuery: 3,
      stylePath: "assets/components/ms_cdek2/css/web/suggestions.css",
      addressFieldsKeys: {
        index: "postCode",
        region: "regionName",
        city: "cityName",
      },
    }
    this.events = {
      select: 'mscdek:select'
    };
    this.config = Object.assign(defaults, config);
    this.field = document.querySelector(this.config.fieldSelector);
    this.initialize();
  }

  initialize() {
    window.msCDEK.loadStyles(this.config.stylePath);

    document.addEventListener('input', e => {
      if (e.target.closest(this.config.fieldSelector)) {
        this.getSuggestions(e.target.closest(this.config.fieldSelector));
      }
    })

    document.addEventListener('click', e => {
      e.target.closest(this.config.suggestionSelector) && this.selectSuggestion(e.target.closest(this.config.suggestionSelector))
    })

    this.field.addEventListener('blur', e => {
      setTimeout(() => {
        this.clearSuggestions(this.field)
      }, 300);
    })
  }

  async getSuggestions(submitter) {
    this.clearSuggestions(submitter);
    if (submitter.value < this.config.minQuery) return;
    const value = submitter.value.replaceAll(',', '-').replaceAll(' ', '-');
    const params = {
      q: value,
      name_startsWith: value
    }
    const url = this.config.actionUrl + '?' + new URLSearchParams(params).toString();
    const result = await window.msCDEK.sendRequest(url, {}, {}, 'GET');
    if (result.geonames && result.geonames.length) {
      this.renderSuggestions(submitter, result.geonames);
    }
  }

  clearSuggestions(submitter) {
    const wrapper = submitter.parentNode.closest(this.config.wrapperSelector) || submitter.parentNode.querySelector(this.config.wrapperSelector);
    wrapper && wrapper.remove();
  }

  renderSuggestions(submitter, suggestions) {
    let wrapper = submitter.parentNode.querySelector(this.config.wrapperSelector);
    if (!wrapper) {
      submitter.insertAdjacentHTML('afterend', this.config.wrapperTpl);
      wrapper = submitter.parentNode.querySelector(this.config.wrapperSelector);
    }
    for (let i = 0; i < suggestions.length; i++) {
      const element = this.config.rowTpl.replace('${data}', JSON.stringify(suggestions[i])).replace('${title}', suggestions[i].cityName);
      wrapper.insertAdjacentHTML('beforeend', element);
    }
  }

  async selectSuggestion(submitter) {
    const data = JSON.parse(submitter.dataset[this.config.dataAttr]);
    const addressFieldsKeys = this.config.addressFieldsKeys;
    if (!data) return;

    if (!document.dispatchEvent(new CustomEvent(this.events.select, {
      bubbles: true,
      cancelable: true,
      detail: {
        data: data,
        submitter: submitter,
      }
    }))) {
      return;
    }
    window.msCDEK.setCookie('cityCode', data.id);
    for (let k in addressFieldsKeys) {
      const field = document.querySelector(`[name="${k}"]`);
      const value = data[addressFieldsKeys[k]] ? data[addressFieldsKeys[k]] : '';
      if (field) {
       setTimeout(() => {
         miniShop2.Order.add(k, value);
        }, 500)
      }
    }
    miniShop2.Order.getcost();
    this.clearSuggestions(submitter)
  }
}
