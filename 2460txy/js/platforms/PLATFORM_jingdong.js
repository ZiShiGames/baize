var cpNickname;
var cpLevel;
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
    this.g2b.loadScript("http://h5.huaer.com.cn/Public/static/xigusdk/xgh5sdk.js?" + new Date().getTime(), function() {
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);

    });
};

pf.prototype.pay = function (amount, orderData) {
    // console.log("amount " + amount);
    // console.log("orderData " + JSON.stringify(orderData));
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
            "?props_name=" + param.goodsName +
            "&trade_no=" + generate_order_id +
            "&amount=" + readl_money;

        this.g2b.getDataXHR(url, function (response) {
            console.log('ccat userId: ', userId);
            console.log('ccat d.user_id', response.d.user_id);

            if (response.c == 0) {

                var jsondata = {
                    "amount": readl_money,
                    "channelExt": response.d.channelExt,
                    "game_appid": response.d.game_appid,
                    "props_name": param.goodsName,
                    "sdkloginmodel": response.d.sdkloginmodel,
                    "trade_no": generate_order_id,
                    "user_id": response.d.user_id,
                    "sign": response.d.sign,
                    "server_id": cpSrvid,
                    "server_name": cpSrvid,
                    "role_id": cpRoleid,
                    "role_name": cpNickname
                }

                xgGame.h5paySdk(jsondata,function(data){
                    console.log(data)
                });

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
        cpSrvid = srvid;
        cpRoleid = cproleid;
        cpNickname = data.rolename;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });

        // 角色上报给渠道
        var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "/" + this.passData.appId +
        "?server_id=" + cpSrvid +
        "&role_id=" + cpRoleid +
        "&role_name=" + cpNickname +
        "&level=" + cpLevel;
        this.g2b.getDataXHR(url, function(response) {
            console.log('level up');
            console.log(response);
            var jsondata = {
                "user_id": response.d.user_id,
                "game_appid": response.d.game_appid,
                "server_id": cpSrvid,
                "server_name": cpSrvid,
                "role_id": cpRoleid,
                "role_name": cpNickname,
                "level": cpLevel,
                "sign": response.d.sign
            }
            xgGame.jointCreateRole(jsondata);//jsondata 为json 字符串非json 对象
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
    xgGame.shareTips({
        game_appid: that.pf_params.game_appid,
    },function(data){
        console.log(data);
    });

    xgGame.shareSdk({
        game_appid: that.pf_params.game_appid,
        title: that.shareInfo.title,
        desc: that.shareInfo.describe
    },function(data){
        //分享结果 status 1 分享成功 0 分享失败
        if(data.status == 1) {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        }
        console.log(JSON.stringify(data));
    });
}
pf.prototype.isDownloadable = function () {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
