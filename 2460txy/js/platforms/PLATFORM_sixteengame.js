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
    console.log('init done');
    var that = this;
    // var self = this;

    this.g2b.loadScript("http://gameapi.16you.com/media/js/16yougame_sdk.min.js", function() {
        var sdk = window.LWGAME_SDK;
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);

        var params = {
            share: {
                friend: {
                    title: that.shareInfo.title,
                    desc: that.shareInfo.desc,
                    imgUrl: that.shareInfo.imgUrl,
                    success: function() {
                        /*分享好友成功回调*/
                        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                    },
                    cancel: function() {
                        /*分享好友取消的回调*/
                        return false;
                    }
                },
                timeline: {
                    title: that.shareInfo.title,
                    imgUrl: that.shareInfo.imgUrl,
                    success: function() {
                        /*分享朋友圈成功回调*/
                        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                    },
                    cancel: function() { /*分享朋友圈取消或失败回调*/ }
                }
            },
            subecribe: {
                success: function() { /*关注成功回调*/ },
                faile: function() { /*关注失败回调*/ }
            },
            pay: {
                success: function() {
                    /*支付成功回调*/
                    console.log("支付成功");
                },
                cancel: function() {
                    /*支付失败回调*/
                    console.log("支付失败");
                }
            }
        };


        LWGAME_SDK.config(params);
    });
};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    var that = this;
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
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount; // for test
        // var readl_money = 1; // for test


        var url = "http://" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?out_trade_no=" + generate_order_id +
            "&product_id=" + readl_money +
            "&total_fee=" + readl_money +
            "&body=" + param.goodsName +
            "&detail=" + param.goodsName +
            "&attach=" + param.ext;

        console.log("url: " + url);

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var out_trade_no = response.d.out_trade_no;
                var product_id = response.d.product_id;
                var total_fee = response.d.total_fee;
                var body = response.d.body;
                var detail = response.d.detail;
                var attach = response.d.attach;
                var sign = response.d.sign;

                var params = {
                    out_trade_no: out_trade_no,
                    product_id: product_id,
                    total_fee: total_fee,
                    body: body,
                    detail: detail,
                    attach: attach,
                    sign: sign
                }

                LWGAME_SDK.pay(params);
            }
        });

    });
};
pf.prototype.checkFocus = function(data) {

    // var url = "http://h5sdk.zytxgame.com/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // }
    // });

};
pf.prototype.reportData = function(data) {
    console.log(JSON.stringify(data));
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    var that = this;
    // sdk.showShare(
    //     title: that.shareInfo.title,
    //     desc: that.shareInfo.desc,
    //     imgUrl: that.shareInfo.imgUrl,
    //
    // });
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
