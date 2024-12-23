export default class MainHandler {
  constructor(config) {
    if (window.msCDEK && window.msCDEK.MainHandler) return window.msCDEK.MainHandler;

    this.events = {
      beforeGetStatus: 'mscdek:status:before',
      afterGetStatus: 'mscdek:status:after'
    };
    this.config = config;

    this.initialize();
  }

  async initialize() {
    window.msCDEK.loadStyles(this.config.stylePath);

    this.getHTMLNodes();

    miniShop2.Order.add = this.orderAddDefaultHandler

    miniShop2.Callbacks.add('Order.add.response.success', 'calculate_cdek', async (response) => {
      await window.msCDEK.MainHandler.orderAddHandler(response);
    });

  }

  orderAddDefaultHandler(key, value){
    const callbacks = miniShop2.Order.callbacks;
    const old_value = value;
    callbacks.add.response.success = function (response) {
      if(!response.data){
        return;
      }
      for(let key in response.data){
        let field = document.querySelector(miniShop2.Order.order + ' [name="' + key + '"]');
        if(field){
          !['delivery','payment'].includes(key) && (field.value = response.data[key]);
          field.closest(miniShop2.Order.inputParent).classList.remove('error');
          field.classList.remove('error');
        }
        if(key === 'delivery'){
          field = document.querySelector(miniShop2.Order.deliveryInputUniquePrefix + response.data[key]);
          if (response.data[key] != old_value) {
            field.dispatchEvent(new Event('click', {bubbles: true}));
          } else {
            miniShop2.Order.getrequired(response.data[key]);
            miniShop2.Order.updatePayments(JSON.parse(field.dataset.payments));
            miniShop2.Order.getcost();
          }
        }
        if(key === 'payment'){
          field = $(miniShop2.Order.paymentInputUniquePrefix + response.data[key]);
          if (response.data[key] != old_value) {
            field.dispatchEvent(new Event('click', {bubbles: true}));
          } else {
            miniShop2.Order.getcost();
          }
        }
      }
    };
    callbacks.add.response.error = () => {
      (function (key) {
        let field = document.querySelector(miniShop2.Order.order + ' [name="' + key + '"]');
        if(['checkbox', 'radio'].includes(field.type)){
          field.closest(miniShop2.Order.inputParent).classList.add('error');
        }else{
          field.classList.add('error');
        }
      })(key);
    };

    const data = {
      key: key,
      value: value
    };
    data[miniShop2.actionName] = 'order/add';
    miniShop2.send(data, miniShop2.Order.callbacks.add, miniShop2.Callbacks.Order.add);
  }

  getHTMLNodes() {
    const deliveriesWrapperSelector = miniShop2.Order.deliveries || '#deliveries';
    this.statusBlock = document.querySelector(this.config.statusId);
    this.mapBlock = document.querySelector(this.config.mapId);
    this.submitButton = document.querySelector('[value="order/submit"]');
    const deliveriesWrapper = document.querySelector(deliveriesWrapperSelector);
    const statusId = this.config.statusId.replace('#', '');

    if (!this.statusBlock) {
      const html = '<div id="' + statusId + '_point"></div><div id="' + statusId + '"></div><input type="hidden" name="point"><input type="hidden" name="pvz_id">';
      deliveriesWrapper && deliveriesWrapper.insertAdjacentHTML('beforeend', html);
      this.statusBlock = document.querySelector(this.config.statusId);
    } else {
      const html = '<div id="' + statusId + '_point"></div><input type="hidden" name="point"><input type="hidden" name="pvz_id">';
      deliveriesWrapper && deliveriesWrapper.insertAdjacentHTML('beforeend', html);
      this.pointField = document.querySelector('[name="point"]');
      this.pvzIdField = document.querySelector('[name="pvz_id"]');
    }
    this.pointBlock = document.querySelector(this.config.statusId + '_point');
  }

  async orderAddHandler(response) {
    if (response.data.delivery) {
      await this.handleDeliveryChange();
    }

    if (response.data.city) {
      if(typeof (this.Widjet) !== 'undefined'){
        this.Widjet.city.set(response.data.city);
      }
      await this.getStatus();
    }
  }

  async handleDeliveryChange() {
    this.statusBlock.innerHTML = '';
    this.statusBlock.classList.add(this.config.hideClass);
    this.submitButton.disabled = true;

    switch (this.getType()) {
      case 'delivery':
        this.displayLoading();
        this.pointBlock && (this.pointBlock.innerHTML = '');
        this.mapBlock && this.mapBlock.classList.add(this.config.hideClass);
        await this.getStatus();
        break;

      case 'points':
        if (!this.mapBlock) {
          this.addMapBlock()
        }

        if (!this.Widjet) {
          this.config.widjet.onCalculate = this.onCalculate;
          this.config.widjet.onChoose = this.onChoose;
          await this.initWidjet();
        }

        this.mapBlock.scrollIntoView({behavior: 'smooth', block: 'start', inline: 'nearest'});
        this.mapBlock.classList.remove(this.config.hideClass)
        break;

      default:
        this.resetData();
        break;
    }
  }

  getType() {
    const selectedDelivery = document.querySelector('[name="delivery"]:checked');
    if (selectedDelivery) {
      const deliveryId = parseInt(selectedDelivery.value);

      if (this.config.deliveries.includes(deliveryId)) {
        return 'delivery';
      }
      if (this.config.points.includes(deliveryId)) {
        return 'points';
      }
    }

    return '';
  }

  displayLoading() {
    this.statusBlock.innerHTML = '<img src="' + this.config.actionUrl.replace('action.php', '') + 'img/loading.gif" width="24" height="24">';
  }

  onCalculate() {
    const widjetPVZ = document.querySelector('.CDEK-widget__delivery-type__item[data-delivery-type="pvz"]');
    widjetPVZ && widjetPVZ.dispatchEvent(new Event('click'));
  }

  async onChoose(response) {
    const $this = window.msCDEK.MainHandler;
    $this.displayLoading();
    $this.setPointData(response);

    await $this.getPointAddress(response);
    await $this.getStatus();
  }

  setPointData(response) {
    this.pointBlock && (this.pointBlock.innerHTML = 'Выбран ПВЗ: <strong>' + response.PVZ.Address + ' (' + response.id + ')' + '</strong>');
    this.pointBlock && this.pointBlock.scrollIntoView({behavior: 'smooth', block: 'start', inline: 'nearest'});
    this.pointField && (this.pointField.value = response.PVZ.Name + ' (' + response.PVZ.Address + ') - ' + response.id);
    this.pvzIdField && (this.pvzIdField.value = response.id);
  }

  async initWidjet() {
    const result = await this.sendRequest({action: 'defaultCity'});
    if (result.success) {
      this.setFieldsValue(result.data);
      this.submitButton.disabled = true;
      this.config.widjet.defaultCity = result.data.city;
      this.Widjet = new ISDEKWidjet(this.config.widjet);
    }
  }

  setFieldsValue(data) {
    for (let k in data) {
      const field = document.querySelector(miniShop2.Order.order + ' [name="' + k + '"]');
      field && (field.value = data[k]);
    }
  }

  async getStatus() {
    const result = await this.sendRequest({action: 'getStatus'});

    if(!document.dispatchEvent(new CustomEvent(this.events.beforeGetStatus, {
      bubbles: true,
      cancelable: true,
      detail: { result: result }
    }))){
      return;
    }

    if (result.success) {
      this.submitButton.disabled = false;
      this.statusBlock.innerHTML = result.status;
      this.statusBlock.classList.remove(this.config.hideClass);
      miniShop2.Order.getcost();
    } else {
      if (result.status !== null) {
        this.statusBlock.innerHTML = '<strong>' + result.status + '</strong>';
        this.statusBlock.classList.remove(this.config.hideClass);
        miniShop2.Message.error(result.status);
      }
    }

    document.dispatchEvent(new CustomEvent(this.events.afterGetStatus, {
      bubbles: true,
      cancelable: false,
      detail: { result: result }
    }))
  }

  async getPointAddress(response) {
    const result = await this.sendRequest({action: 'getPointAddress', city_code: response.city, point: response.id});

    if (result.success) {
      window.msCDEK.setCookie('cityCode', result.data.city_code);
      for(let k in result.data) {
        miniShop2.Order.add(k, result.data[k]);
      }
    }
  }

  addMapBlock() {
    const mapId = this.config.mapId.replace('#', '');
    const wrapper = miniShop2.Order.order || '#msOrder';
    const actionName = miniShop2.actionName || 'ms2_action';
    const placeSelector = wrapper + ' [name="' + actionName + '"][value="order/clean"]';
    const place = document.querySelector(placeSelector) || document.querySelector(this.config.statusId);
    place && place.insertAdjacentHTML('afterend', '<div id="' + mapId + '"></div>');
    this.mapBlock = document.querySelector(this.config.mapId);
  }

  resetData() {
    this.mapBlock && this.mapBlock.classList.add(this.config.hideClass);
    this.statusBlock && this.statusBlock.classList.add(this.config.hideClass);
    this.pointBlock && (this.pointBlock.innerHTML = '');
    this.submitButton && (this.submitButton.disabled = false);
    this.pointField && (this.pointField.value = '');
    this.pvzIdField && (this.pvzIdField.value = '');
    window.msCDEK.setCookie('cityCode', '');
  }

  async sendRequest(data) {
    const params = new FormData();
    for (let k in data) {
      params.append(k, data[k]);
    }
    return await window.msCDEK.sendRequest(this.config.actionUrl, params);
  }
}
