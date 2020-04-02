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
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
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
    param.platform = 'allu';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount / 100; // for test

        // var jump_to = 'http://localhost:8888/index.php/wxpay/showQrCode?data=weixin://wxpay/bizpayurl?pr=JTqVq4r';
        //     window.top.location.href = jump_to;

        // 测试
        var res_url = 'http://localhost:8888/index.php/wxpay/showQrCode?data=weixin://wxpay/bizpayurl?pr=JTqVq4r';
        that.g2b.loadScript("http://h5.allugame.com/js/iframe.js", function () {
            iframePay.open(res_url, function () {
                console.log("onclose");
            });
        })

        /*
        以下先注释，测试完后需要打开
        */

        // this.g2b.loadScript("http://h5.xileyougame.com/js/iframe.js", function() {
        //     var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/allutest/" + that.passData.appId + "?order_id=" + generate_order_id +
        //         "&money=" + readl_money + "&openId=" + orderData.openId + "&userId=" + userId + "&goodsName=" + encodeURIComponent(param.goodsName);
        //     // if (param.appId == 1084 || param.appId == 1087) {
        //     // 	var url = "http://114.215.128.127/index.php/api/sign_order/allu/" + that.passData.appId + "?order_id=" + generate_order_id +
        //     // 		"&money=" + readl_money + "&openId=" + orderData.openId + "&userId=" + userId + "&goodsName=" + encodeURIComponent(param.goodsName);
        //     // }

        //     console.log(' get in h5 iframe and url is ' + url);
        //     this.g2b.getDataXHR(url, function(response) {
        //         if (response.c == 0) {
        //             if (response.d.agent == 1) {
        //                 var notify = "http://h5sdk.zytxgame.com/index.php/api/notify/allutest/" + param.appId;
        //                 console.log(' get in ajax and notify is ' + notify);
        //                 var jump_to = 'http://h5.xileyougame.com/index.php/api/order?' +
        //                     "uid=" + userId +
        //                     "&game_id=" + param.appId +
        //                     "&orderNo=" + generate_order_id +
        //                     "&goodsName=" + encodeURIComponent(param.goodsName) +
        //                     "&gameName=" + encodeURIComponent("龙城霸业") +
        //                     "&money=" + amount +
        //                     "&notify=" + encodeURIComponent(notify);
        //                 window.top.location.href = jump_to;
        //                 console.log(' get in ajax and notify is ' + jump_to);
        //             } else {

        //                 var url = response.d.pay_url;
        //                 console.log(url);
        //                 window.top.location.href = url;
        //                 // iframePay.open(url, function() {
        //                 // 	console.log("onclose");
        //                 // });
        //             }

        //         }
        //     });

        // })
    });
};
pf.prototype.checkFocus = function(data) {

    var url = "http://h5sdk.zytxgame.com/index.php/api/focus/allu?openid=" + data.openId;
    this.g2b.getDataXHR(url, function(response) {
        if (response.c == 0) {
            this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, 1);
        } else {
            this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, 1);
        }
    });

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
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/allu/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
    window.top.postMessage({
        cmd: "showFocus"
    }, "*");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
