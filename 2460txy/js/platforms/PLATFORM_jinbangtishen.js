var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {


    var that = this;
    that.g2b.loadScript("https://cdn.99kgames.com/js/tpgame_sdk.min.js", function() {
        var params = {
            share: {
                friend: {
                    title: '龙城霸业',
                    desc: '百万元宝悬赏，邀兄弟共战龙城',
                    imgUrl: 'http://h5.xileyougame.com/img/icon/75-751.png',
                    success: function() {
                        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                    },
                    cancel: function() {
                        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, false);
                    },
                },
                timeline: {
                    title: '龙城霸业',
                    imgUrl: 'http://h5.xileyougame.com/img/icon/75-751.png',
                    success: function() {
                        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                    },
                    cancel: function() {
                        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, false);
                    },
                }
            },
            pay: {
                success: function() { /*支付成功回调*/ },
                cancel: function() { /*支付失败回调*/ },
            }
        };
        TPGAME_SDK.config(params);
    });
    that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;

        var real_money = amount;
        // var real_money = 1; //  for test

        var params = {
            out_trade_no: generate_order_id,
            product_id: 1,
            total_fee: real_money,
            body: orderData.subject,
            detail: orderData.subject,
            attach: ''
        };

        var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/jinbangtishen/" + param.appId + "?content=" + encodeURIComponent(JSON.stringify(params));
        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                params['sign'] = response.d;
                console.log(JSON.stringify(params));
                TPGAME_SDK.pay(params);
            }
        });
        closePayWindow();

    });
};
pf.prototype.checkFocus = function(data) {

    var url = "http://h5sdk.zytxgame.com/index.php/api/focus/jinbangtishen?openid=" + data.openId;
    this.g2b.getDataXHR(url, function(response) {
        if (response.c == 0) {
            this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, response.d);
        } else {
            this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
        }
    });
};
pf.prototype.reportData = function(data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/jinbangtishen/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    TPGAME_SDK.showQRCode();
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
