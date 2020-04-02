var cpChannelExt;
var cpUser_id;
var cpSdkloginmodel;
var server_id;
var server_name;
var role_id;
var role_name;
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
    this.g2b.loadScript("//www.1n.cn/Public/static/xigusdk/xgh5sdk.js?v=" + new Date().getTime(), function() {
        cpChannelExt = that.pf_params.channelExt;
        cpUser_id = that.pf_params.user_id;
        cpSdkloginmodel = that.pf_params.sdkloginmodel;
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


        var url = "//"+location.host+"/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?amount=" + readl_money + "&channelExt=" + cpChannelExt + "&props_name=" + param.goodsName +
            "&trade_no=" + generate_order_id + "&user_id=" + cpUser_id + "&sdkloginmodel=" + cpSdkloginmodel;

        this.g2b.getDataXHR(url, function(response) {
            // console.log(cpChannelExt);
            if (response.c == 0) {
                var jsondata = {
                    'amount': readl_money,
                    'channelExt': cpChannelExt,
                    'game_appid': response.d.appid,
                    'props_name': param.goodsName,
                    'trade_no': generate_order_id,
                    'user_id': cpUser_id,
                    'sdkloginmodel': cpSdkloginmodel,
                    'sign': response.d.sign,
                    'server_id': server_id,
                    'server_name': server_name,
                    'role_id': role_id,
                    'role_name': role_name,
                }
                xgGame.h5paySdk(jsondata, function(data) {
                    console.log(data);
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

        server_id = srvid;
        server_name = srvid;
        role_id = roleid;
        role_name = data.rolename;
        var url = "//"+location.host+"/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//"+location.host+"/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//"+location.host+"/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
    alert('点击右上角分享至微信好友');
    xgGame.shareSdk({
        game_appid: that.pf_params.game_appid,
        title: that.shareInfo.title,
        desc: that.shareInfo.desc
    }, function(data) { //分享结果status  1分享成功   0分享失败
        console.log(data);
        if (data.status == 1) {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        } else {
            alert('分享失败');
        }

    });

}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
