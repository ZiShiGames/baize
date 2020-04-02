var _nickName = '';
var cp_uid = '';
var cp_gameId = '';
var cp_time = '';
var cp_signType = '';

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
    cp_uid = this.pf_params.uid;
    cp_gameId = this.pf_params.gameId;
    cp_time = this.pf_params.time;
    cp_signType = this.pf_params.signType;


    var that = this;
    this.g2b.loadScript("http://h5game.boc7.net/static/sdk/cy.sdk.js", function() {

        var sdk = window.CY_GAME_SDK;
        var params = {
            gameId: cp_gameId, //游戏的ID
            share: {
                success: function() { /*分享好友成功回调*/
                    alert("game tell share success"); //该方法仅供参考
                    that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);

                }
            },
            pay: {
                success: function() {
                    console.log("game tell pay success"); //该方法仅供参考
                }
            }
        };
        sdk.config(params);

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
    param.ext = orderData.ext || ""; // serverid || 区服
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject; // 商品名
    param.cproleid = orderData.cproleid;
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id; // 我们的订单号 || ext
        var userId = res.d.userId;
        var gamerate = amount / 10;
        var readl_money = amount / 100; // for test


        var url = "http://" + location.host + "/index.php/api/sign_order/" + param.platform + "/" + param.appId +
            "?gameId=" + cp_gameId +
            "&uid=" + cp_uid +
            "&time=" + cp_time +
            "&server=" + param.ext +
            "&role=" + userId +
            "&goodsId=" + readl_money +
            "&goodsName=" + param.goodsName +
            "&money	=" + readl_money +
            "&cpOrderId=" + generate_order_id +
            "&ext=" + generate_order_id +
            "&signType=" + cp_signType;

        console.log("url: " + url);


        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var gameId = response.d.gameId;
                var uid = response.d.uid;
                var time = response.d.time;
                var server = response.d.server;
                var role = response.d.role;
                var goodsId = response.d.goodsId;
                var goodsName = response.d.goodsName;
                var money = response.d.money;
                var cpOrderId = response.d.cpOrderId;
                var ext = response.d.ext;
                var sign = response.d.sign;
                var signType = response.d.signType;

                sdk.pay({
                    gameId: gameId,
                    uid: uid,
                    time: time, //10位时间戳
                    server: server,
                    role: role,
                    goodsId: goodsId,
                    goodsName: goodsName,
                    money: money,
                    cpOrderId: cpOrderId,
                    ext: ext,
                    sign: sign,
                    signType: signType
                });

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
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    var that = this;
    sdk.showShare({
        title: that.shareInfo.title,
        desc: that.shareInfo.desc,
        imgUrl: that.shareInfo.imgUrl,

    });
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
