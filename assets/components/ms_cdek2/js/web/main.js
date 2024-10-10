var ms_CDEK2 = {
    config: {},
    init: function(config){
        this.config = config;
        if (this.config.autocomplete) {
            this.initAutoComplete();
        }
        this.config.deliveries_wrapper = miniShop2.Order.deliveries || '#deliveries';
        
        document.addEventListener('DOMContentLoaded', function(){
            status_id = ms_CDEK2.config.status_id.replace('#', '');
            if ($(ms_CDEK2.config.status_id).length == 0) {
                $(deliveries).append('<div id="' + status_id + '_point"></div><div id="' + status_id + '"></div><input type="hidden" name="point"><input type="hidden" name="pvz_id">');
            } else {
                $(ms_CDEK2.config.status_id).before('<div id="' + status_id + '_point"></div>');
                $(ms_CDEK2.config.status_id).after('<input type="hidden" name="point"><input type="hidden" name="pvz_id">');
            }
        });
        
        miniShop2.Callbacks.add('Order.add.response.success', 'calculate_cdek', function(response) {
            if (response.data.city && typeof(ms_CDEK2.Widjet) != 'undefined') {
                ms_CDEK2.Widjet.city.set(response.data.city);
                ms_CDEK2.calculate();
            }
            if (response.data.delivery) {
                ms_CDEK2.calculate();
            }
            if (response.data.index) {
                ms_CDEK2.displayLoading();
                setTimeout(function(){
                    ms_CDEK2.calculate();
                }, 800);
            }
        });
        
        document.addEventListener('DOMContentLoaded', function(){
            ms_CDEK2.calculate();
        });
    },
    calculate: function(){
        $(ms_CDEK2.config.status_id).html('');
        $('[value="order/submit"]').prop('disabled', true);
        switch (this.getType()) {
            case 'delivery':
                $(ms_CDEK2.config.status_id + '_point').html('');
                this.displayLoading();
                $(this.config.map_id).hide();
                $.post(ms_CDEK2.config.action_url, {action: 'getStatus'}, function(data) {
                    if (data.success) {
                        $('[value="order/submit"]').prop('disabled', false);
                        $(ms_CDEK2.config.status_id).html(data.status).show();
                        miniShop2.Order.getcost();
                    } else {
                        if (data.status !== null) {
                            $(ms_CDEK2.config.status_id).html('<strong>' + data.status + '</strong>').show();
                            miniShop2.Message.error(data.status);
                        }
                    }
                }, 'json');
                break;
            case 'points':
                if ($(this.config.map_id).length == 0) {
                    map_id = ms_CDEK2.config.map_id.replace('#', '');
                    var wrapper = miniShop2.Order.order || '#msOrder';
                    var actionName = miniShop2.actionName || 'ms2_action';
                    var place = wrapper + ' [name="' + actionName + '"][value="order/clean"]';
                    if ($(place).length == 0) {
                        place = ms_CDEK2.config.status_id;
                    }
                    $(place).after('<div id="' + map_id + '"></div>');
                }
                if (!ms_CDEK2.Widjet) {
                    $(ms_CDEK2.config.map_id).css({height: '600px', 'margin-top': '20px', background: '#ededed'});
                    this.config.widjet.onCalculate = function(response){
                        $('.CDEK-widget__delivery-type__item[data-delivery-type="pvz"]').click();
                    }
                    this.config.widjet.onChoose = function(response){
                        ms_CDEK2.displayLoading();
                        $(ms_CDEK2.config.status_id + '_point').html('<strong>' + response.PVZ.Name + '</strong> ' + response.PVZ.Address + ' (' + response.id + ')');
                        $('body,html').animate({scrollTop: $(ms_CDEK2.config.status_id + '_point').first().offset().top - 200}, 300);
                        $('[name="point"]').val(response.PVZ.Name + ' (' + response.PVZ.Address + ') - ' + response.id);
                        $('[name="pvz_id"]').val(response.id);
                        $(miniShop2.Order.order + ' [name="city"]').val(response.cityName);
                        
                        $.post(ms_CDEK2.config.action_url, {action: 'getPointIndex', city_code: response.city, point: response.id }, function(data) {
                            if (data.success) {
                                $(miniShop2.Order.order + ' [name="index"]').val(data.index).change();
                                
                                setTimeout(function(){
                                    $.post(ms_CDEK2.config.action_url, {action: 'getStatus'}, function(data) {
                                        if (data.success) {
                                            var html = $(ms_CDEK2.config.status_id).html();
                                            $('[value="order/submit"]').prop('disabled', false);
                                            $(ms_CDEK2.config.status_id).html(data.status).show();
                                            miniShop2.Order.getcost();
                                        } else {
                                            if (data.status !== null) {
                                                $(ms_CDEK2.config.status_id).html('<strong>' + data.status + '</strong>').show();
                                                miniShop2.Message.error(data.status);
                                            }
                                        }
                                    }, 'json');
                                }, 500);
                            }
                        }, 'json');

                    }
                    $.post(ms_CDEK2.config.action_url, {action: 'defaultCity'}, function(data) {
                        if (data.success) {
                            $('[value="order/submit"]').prop('disabled', true);
                            ms_CDEK2.config.widjet.defaultCity = data.city;
                            ms_CDEK2.Widjet = new ISDEKWidjet(ms_CDEK2.config.widjet);
                        }
                    }, 'json');
                }
                setTimeout(function(){
                    $('body,html').animate({scrollTop: $(ms_CDEK2.config.map_id).first().offset().top - 200}, 300);
                }, 300);
                $(this.config.map_id).show();
                break;
            default:
                $(this.config.map_id).hide();
                $(this.config.status_id).hide();
                $(this.config.status_id + '_point').html('');
                $('[value="order/submit"]').prop('disabled', false);
                break;
        }
    },
    displayLoading: function(){
        $(ms_CDEK2.config.status_id).html('<img src="' + this.config.action_url.replace('action.php', '') + 'img/loading.gif" width="24" height="24">');
    },
    getType: function(){
        var delivery_id = parseInt($(this.config.deliveries_wrapper).find('input:checked').val());
        if (this.config.deliveries.indexOf(delivery_id) >= 0) {
            return 'delivery';
        }
        if (this.config.points.indexOf(delivery_id) >= 0) {
            return 'points';
        }
        return '';
    },
    initAutoComplete: function(){
        var ms_cdek2_city_ac = new autoComplete({
            selector: 'input[name="city"]',
            minChars: 3,
            source: function (term, response) {
                term = term.toLowerCase();
                $.getJSON('https://api.cdek.ru/city/getListByTerm/jsonp.php?callback=?', {
                    q: term,
                    name_startsWith: term,
                }, function (data) {
                    var cityArr = data.geonames;
                    if (cityArr) {
                        var suggestions = [];
                        for (i = 0; i < cityArr.length; i++) {
                            suggestions.push(cityArr[i]);
                        }
                        response(suggestions);
                    }
                });
            },
            renderItem: function (item, search) {
                if (item.postCodeArray) {
                    this.index = item.postCodeArray[0];
                    /*
                    if (item.postCodeArray[1]) {
                        this.index = item.postCodeArray[1];
                    } else {
                        this.index = item.postCodeArray[0];
                    }
                    */
                } else {
                    this.index = 0;
                }
                return '<div class="autocomplete-suggestion" data-index="' + this.index + '" data-codes=\'' + JSON.stringify(item.postCodeArray) + '\' data-id="' + item.id + '" data-region="' + item.regionName + '" data-city="' + item.cityName + '" data-country="' + item.countryName + '" data-val="' + item.cityName + '">' + item.cityName + ' <small>[' + item.regionName + ']</small></div>';
            },
            onSelect: function (e, term, item) {
                $('[name="city"]').val($(item).data('city')).change();
                setTimeout(function(){
                    if (typeof(ms_CDEK2.Widjet) != 'undefined') {
                        ms_CDEK2.Widjet.city.set($(item).data('id'));
                    }
                }, 500);

                setTimeout(function(){
                    if ($('[name="index"]').val()) {
                        if ($(item).data('codes').indexOf($('[name="index"]').val()) < 0) {
                            $('[name="index"]').val($(item).data('index'));
                        }
                    } else {
                        $('[name="index"]').val($(item).data('index'));
                    }
                    $('[name="country"]').val($(item).data('country'));
                    setTimeout(function(){
                        $('[name="region"]').val($(item).data('region'));
                    }, 300);
                    
                    $('[name="index"]').change();
                    $('[name="country"]').change();
                    $('[name="region"]').change();
                    $('[name="street"]').focus();
                }, 1000);
            }
        });
    }
}
