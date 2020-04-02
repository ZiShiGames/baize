var cpNickname;
var cpLevel;
var pf = function (g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function () {
    console.log('init done');
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function (amount, orderData) {
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
    }, function (res) {
        console.log("1 ", JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount; // for test
        //
        // var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
        //     "?money=" + amount +
        //     "&productId=" + amount +
        //     "&productName=" + param.goodsName +
        //     "&gameUid=" + userId +
        //     "&gameOrderno=" + generate_order_id;
        //     console.log('url: ',url);
        //
        // this.g2b.getDataXHR(url, function(response) {
        //     if (response.c == 0) {
        //         var money = response.d.money; // 支付金额
        //         var productId = response.d.productId; // 游戏道具ID
        //         var productName = response.d.productName;  // 游戏道具名称
        //         var gameUid = response.d.gameUid;
        //         var gameOrderno = response.d.gameOrderno;

        window.XYOU_SDK.pay({
            cost: readl_money,
            product_id: readl_money,
            productName: param.goodsName,
            game_uid: param.cproleid,
            game_orderno: generate_order_id,
            app_ext1: generate_order_id
        });
        //     }
        // });

    });
};
pf.prototype.checkFocus = function (data) {

    // var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    var that = this;
    console.log("focus", window.focusStage);
    window.XYOU_SDK.getUserInfo(function (data) {
        //获取用户关注微信公众号的实时状态
        var s = window.XYOU_SDK.getWxSubscribe();
        if (s == 0) {
            that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, 0);
        } else if (s == 1) {
            that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, 1);
        } else if (s == 2) {
            that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
        }
    });

};
pf.prototype.reportData = function (data) {
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
        cpLevel = level;
        cpNickname = data.rolename;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));

        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
    window.XYOU_SDK.subscribeWx();
};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function () {
    // console.log('click share button');
    window.XYOU_SDK.share();
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);

}
pf.prototype.isDownloadable = function () {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};