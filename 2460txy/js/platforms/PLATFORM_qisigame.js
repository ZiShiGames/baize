var cp_username = '';
var cp_platform = '';
var cp_time = '';
var cp_appId = '';

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
    cp_username = this.pf_params.username;
    cp_platform = this.pf_params.platform;
    cp_time = this.pf_params.time;
    cp_appId = this.pf_params.appid;

    console.log('init done');

    this.g2b.loadScript("http://m.static.7477.com/wap/js/7477_h5_framesdk.js?v=" + new Date().getTime(), function() {

        // var sdk = window.CY_GAME_SDK;
        this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);

    });
};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    var that = this;
    var param = {};
    param.openId = orderData.openId; // 2460 用户id
    param.openKey = orderData.openKey; // 2460 验证key
    param.appId = this.passData.appId; // 2460 游戏id
    param.money = amount; // 钱 单位分
    param.orderNo = orderData.orderNo; // 研发游戏订单id
    param.ext = orderData.ext || ""; // serverid
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject; //商品名
    param.cproleid = orderData.cproleid;
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id; // 我们的订单号 [ext]
        var userId = res.d.userId;
        var readl_money = amount / 100; // for test
        // var readl_money = 0.01; // for test

        var url = "http://" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?uid=" + userId +
            "&username=" + cp_username +
            "&paymoney=" + readl_money +
            "&appid=" + cp_appId +
            "&serverid=1" +
            "&platform=" + cp_platform +
            "&time=" + cp_time +
            "&out_orderid=" + generate_order_id +
            "&goods_name=" + param.goodsName +
            "&param=" + param.ext;


        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var uid = response.d.uid;
                var username = response.d.username;
                var paymoney = response.d.paymoney;
                var appid = response.d.appid;
                var serverid = response.d.serverid;
                var platform = response.d.platform;
                var time = response.d.time;
                var out_orderid = response.d.out_orderid;
                var goods_name = response.d.goods_name;
                var sign = response.d.sign;
                var param = response.d.param;


                var url_qisigame = "http://m.7477.com/wap/pay?uid=" + uid +
                    "&username=" + username +
                    "&paymoney=" + paymoney +
                    "&appid=" + appid +
                    "&serverid=" + serverid +
                    "&platform=" + platform +
                    "&time=" + time +
                    "&out_orderid=" + out_orderid +
                    "&sign=" + sign +
                    "&goods_name=" + encodeURIComponent(goods_name) +
                    "&param=" + param;

                tc_iframe(url_qisigame);

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
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
