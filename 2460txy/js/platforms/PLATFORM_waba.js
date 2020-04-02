var cpNickname;
var cpLevel;
var cpUserId;
var cpGameAppid;
var cpSrvid;
var cpRoleid;
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
    var that = this;
    this.g2b.loadScript('//waba.wabagame.com/Public/static/xigusdk/xgh5sdk.js?' + new Date().getTime(), function () {
        cpUserId = that.pf_params.user_id;
        cpGameAppid = that.pf_params.game_appid;
        cpSdkloginmodel = that.pf_params.sdkloginmodel;
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
    })
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
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount; // for test

        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?amount=" + readl_money +
            "&channelExt=" + generate_order_id +
            "&game_appid=" + cpGameAppid +
            "&props_name=" + param.goodsName +
            "&sdkloginmodel=" + cpSdkloginmodel +
            "&trade_no=" + generate_order_id +
            "&user_id=" + cpUserId;

        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                // // 支付调起
                var jsondata = {
                    "amount": readl_money,
                    "channelExt": generate_order_id,
                    "game_appid": cpGameAppid,
                    "props_name": param.goodsName,
                    "sdkloginmodel": cpSdkloginmodel,
                    "trade_no": generate_order_id,
                    "user_id": cpUserId,
                    "sign": response.d,
                    "server_id": cpSrvid,
                    "server_name": cpSrvid,
                    "role_id": cpRoleid,
                    "role_name": cpNickname
                };
                // console.log(jsondata);
                xgGame.h5paySdk(jsondata, function (data) {
                    console.log(data);
                    alert('支付成功！请手动关闭支付页面~');
                });

                // 支付跳转
                // var jump_to = "http://waba.wabagame.com/mediawide.php/game/game_pay" + this.g2b.object2search(jsondata);
                // window.top.location.href = jump_to;

            }
        });

    });
};
pf.prototype.checkFocus = function (data) {

    // var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // }
    // });

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
        cpSrvid = srvid;
        cpRoleid = cproleid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });

        // 上报登录信息给渠道
        var focus_url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "/" + this.passData.appId + "?user_id=" + cpUserId + "&game_appid=" + cpGameAppid + "&server_id=" + srvid + "&server_name=" + srvid + "&role_id=" + cproleid + "&role_name=" + cpNickname + "&level=" + level;
        this.g2b.getDataXHR(focus_url, function (response) {
            // console.log(JSON.stringify(response));
            var jsondata = {
                "user_id": cpUserId,
                "game_appid": cpGameAppid,
                "server_id": srvid,
                "server_name": srvid,
                "role_id": cproleid,
                "role_name": cpNickname,
                "level": level,
                "sign": response.d
            };
            xgGame.jointCreateRole(JSON.stringify(jsondata)); //jsondata为json字符串 非json对象
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
    } else if (data.action == 'level_up') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var level = data.rolelevel;
        var cproleid = data.cproleid;
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
};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function () {
    console.log('click share button');
    var that = this;
    xgGame.shareSdk({
        game_appid: cpGameAppid,
        title: that.shareInfo.title,
        desc: that.shareInfo.desc
    }, function (data) { //分享结果status  1分享成功   0分享失败
        console.log(data);
        if (data.status == 1 || data.status == "1") {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        } else {
            alert("分享失败！请重新分享试试~");
        }
    });
}
pf.prototype.isDownloadable = function () {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
