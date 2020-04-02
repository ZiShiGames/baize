var alluGameId;
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
    if (alluGameId == 1021) {
        return;
    };
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

        var url = "http://" + location.host + "/index.php/api/sign_order/jinb/" + param.appId + "?content=" + encodeURIComponent(JSON.stringify(params));
        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                params['sign'] = response.d;

                TPGAME_SDK.pay(params);
            }
        });
        closePayWindow();

    });
};
pf.prototype.checkFocus = function(data) {

    var url = "http://" + location.host + "/index.php/api/focus/jinb?openid=" + data.openId;
    this.g2b.getDataXHR(url, function(response) {
        if (response.c == 0) {
            console.log('jinbang focus ' + response.d.status);
            // alert('jinbang focus ' + response.d);
            this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, response.d.status);
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
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;


        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/jinb/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    // if (this.passData.appId == 1190) {
    //     var div = document.createElement("div");
    //     div.style.position = "fixed";
    //     div.style.zIndex = 99999;
    //     div.style.left = "20%";
    //     div.id = 'showQRCode_1';
    //     div.style.top = "20%";
    //     div.style.width = "60%";
    //     div.style.height = '230px';
    //     div.style.overflow = "auto";
    //     div.style.backgroundColor = "rgb(255, 255, 255)";
    //     div.style.backgroundColor = "rgba(255, 255, 255, 1)";
    //     div.innerHTML = '<h1 style="text-align:center;">关注奖励</h1><br/><p style="text-align:center;">1000元宝</p><br/><button style="margin-left:40%;" type="button" onclick="this.parentNode.style.display=\'none\';TPGAME_SDK.showQRCode();">点击关注</button>';
    //     document.body.appendChild(div);
    //     console.log('ininin');
    // } else {
    TPGAME_SDK.showQRCode();
    // }


};
pf.prototype.isOpenShare = function() {

    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
pf.prototype.showShare = function () {
    console.log('click share button');
    TPGAME_SDK.showShare()
    // alert('分享给好友,即可获得奖励');
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
