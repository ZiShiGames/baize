var cpOpenid;
var cpPlatform;
var cpGameid;
var cpParams;
var cpUid;

var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    p_name = passData.passId;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
    // cpOpenid = this.pf_params.openid;
    // cpPlatform = this.pf_params.platform;
    cpGameid = this.pf_params.gameId;
    cpUid = this.pf_params.uid;

    // cpParams = this.pf_params.params;
    this.g2b.loadScript('http://www.333h5.com/static/sdk/cy.sdk.js', function() {
        var sdk = window.CY_GAME_SDK;
        var that = this;
        var params = {
            gameId: cpGameid, //游戏的ID
            share: {
                success: function() { /*分享好友成功回调*/
                    that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                }
            },
            pay: {
                success: function() { /* 支付成功回调方法（仅针对于快捷支付方式有效）*/ }
            }
        };
        sdk.config(params)
        this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
    });

};

pf.prototype.pay = function(amount, orderData) {
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    // param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
    param.platform = 'threegame';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var user_id = res.d.userId;
        var readl_money = amount/100;
        // var readl_money = 0.01;
        var subject = orderData.subject;
        var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/" + passId + "/" + param.appId + "?order_id=" + generate_order_id +
			"&gameid=" + cpGameid +"&goodsId=" + "1" +"&goodsName=" + subject +"&money=" + readl_money +"&role=" + cpUid + "&server=" + param.ext + "&uid=" + cpUid;
        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {

                sdk.pay({
                    gameId: cpGameid,
                    uid: cpUid,
                    time: response.d.time, //10位时间戳
                    server: param.ext,
                    role: cpUid,
                    goodsId: "1",
                    goodsName: subject,
                    money: readl_money,
                    cpOrderId: generate_order_id,
                    ext: generate_order_id,
                    sign: response.d.sign,
                    signType: "md5"
                });
            }
        });


    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/threegame/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function() {
    var that = this;
    sdk.showShare({
        title: that.shareInfo.title,
        desc: that.shareInfo.desc,
        imgUrl: that.shareInfo.imgUrl
    });
}
