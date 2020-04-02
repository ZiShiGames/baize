var cp_game_appid;
var sdkloginmodel;
var sdklogindomain;
var cp_user_id;
var channelExt;
var name;

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
    this.g2b.loadScript("//" + location.host + "/js/jssdk/md5.js", function() {});
    this.g2b.loadScript("http://www.0713yy.com/Public/static/xigusdk/xgh5sdk.js?v=" + new Date().getTime(), function() {
        cp_game_appid = that.pf_params.game_appid;
        sdkloginmodel = that.pf_params.sdkloginmodel;
        sdklogindomain = that.pf_params.sdklogindomain;
        cp_user_id = that.pf_params.user_id;
        channelExt = that.pf_params.channelExt;
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
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


        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId + "?order_id=" + generate_order_id +
            "&money=" + readl_money + "&openId=" + orderData.openId + "&userId=" + userId + "&goodsName=" + param.goodsName +
            "&cp_game_appid=" + cp_game_appid + "&sdkloginmodel=" + sdkloginmodel + "&channelExt=" + channelExt;

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var jsondata = {
                    "amount": readl_money,
                    "channelExt": channelExt,
                    "game_appid": cp_game_appid,
                    "props_name": response.d.goodsName,
                    "sdkloginmodel": sdkloginmodel,
                    "trade_no": generate_order_id,
                    "user_id": cp_user_id,
                    "sign": response.d.sign,
                    "server_id": param.ext,
                    "server_name": param.ext,
                    "role_id": param.cproleid,
                    "role_name": name
                }
                xgGame.h5paySdk(jsondata, function(data) {
                    console.log(data);
                });

            }
        });

    });
};
pf.prototype.checkFocus = function(data) {


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
        name = data.rolename;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
        var fo_url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "/" + this.passData.appId +
            "?cp_user_id=" + cp_user_id + "&cp_game_appid=" + cp_game_appid + "&server_id=" +
            srvid + "&roleid=" + cproleid +
            "&rolename=" + name + "&level=" + level;
        this.g2b.getDataXHR(fo_url, function(response) {
            var jsondata = {
                "user_id": cp_user_id,
                "game_appid": cp_game_appid,
                "server_id": srvid,
                "server_name": srvid,
                "role_id": cproleid,
                "role_name": response.d.name,
                "level": 1,
                "sign": response.d.sign,
            }
            xgGame.jointCreateRole(jsondata);
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
    xgGame.shareSdk({
        game_appid: cp_game_appid,
        title: that.shareInfo.title,
        desc: that.shareInfo.desc
    }, function(data) { //分享结果status  1分享成功   0分享失败
        if (data.status == 1) {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        };
    });

}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
