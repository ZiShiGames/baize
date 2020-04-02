var _nickName = '';
var cp_gameid = '';

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
    cp_gameid = this.pf_params.gameId;
    var that = this;

    this.g2b.loadScript("http://h.g765.com/Statics/js/api.js", function() {

        var sdk = window.G765_SDK;
        sdk.config(cp_gameid, function() {
            console.log("pay ok");
        }, function() {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        }, function() {

        });
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
    param.goodsName = orderData.subject; // 商品名
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
        // var readl_money = 0.01;


        var url = "http://" + location.host + "/index.php/api/sign_order/" + param.platform + "/" + param.appId +
            "?ext=" + generate_order_id +
            "&goodsName=" + readl_money +
            "&readl_money=" + readl_money +
            "&orderId=" + generate_order_id +
            "&userId=" + userId +
            "&userName=" + userId +
            "&service=" + param.ext;
        console.log("url: " + url);


        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var source = response.d.source;
                var pId = response.d.pId;
                var userId = response.d.userId;
                var userName = response.d.userName;
                var gameId = response.d.gameId;
                var goodsName = response.d.goodsName;
                var money = response.d.money;
                var orderId = response.d.orderId;
                var ext = response.d.ext;
                var time = response.d.time;
                var gameUrl = response.d.gameUrl;
                var sign = response.d.sign;
                var service = response.d.service;

                var url_gqiliuu = "http://h.g765.com/Home/Pay/pay?source=" + source +
                    "&pId=" + pId +
                    "&userId=" + userId +
                    "&userName=" + (userName) +
                    "&gameId=" + gameId +
                    "&goodsName=" + (goodsName) +
                    "&money=" + money +
                    "&orderId=" + orderId +
                    "&ext=" + ext +
                    "&time=" + time +
                    "&gameUrl=" + gameUrl +
                    "&sign=" + sign;
                console.log("http://h.g765.com/api/index/checktoken?source=" + source + "&pId=" + pId + "&userId=" + userId + "&userName=" + (userName) + "&gameId=" + gameId + "&goodsName=" + (goodsName) + "&money=" + money + "&orderId=" + orderId + "&ext=" + ext + "&time=" + time + "&gameUrl=" + (gameUrl)
                +"&sign=" + sign);

                sdk.gamePay(gameId, service, userId, goodsName, orderId, url_gqiliuu);

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

        _nickName = nickName;
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        sdk.createRole(cp_gameid, srvid, roleid);

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
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
