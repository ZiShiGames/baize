var p_name;
var pf = function (g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    p_name = passData.passId;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function () {
    SDK_AREA51.init(2);
    // SDK_AREA51.checkWindow();
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function (amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    var param = {};
    var that = this;
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
    param.cproleid = orderData.cproleid;
    param.platform = p_name;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function (res) {
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var ext = generate_order_id;
        var readl_money = amount / 100;
        // var readl_money = 0.01; // for test

        var url = "//" + location.host + "/index.php/api/sign_order/dayu/" + that.passData.appId + "?order_id=" + generate_order_id +
            "&money=" + readl_money + "&userId=" + userId + "&ext=" + ext;

        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                var params = {
                    'UID': userId,
                    'game_id': response.d.gameid,
                    'recharge': readl_money,
                    'order_id': generate_order_id,
                    'ext': ext,
                    // 'sign': MD5(userId+response.d.game_id+readl_money+generate_order_id+ext+response.d.key).toLowerCase(),
                    'sign': response.d.sign,
                }
                console.log(JSON.stringify(params));
                SDK_AREA51.recharge(params);
                closePayWindow();
            }
        });
        // myOwnBri.startPhone('1');

    });
};
pf.prototype.checkFocus = function (data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function (data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        console.log(url);
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        console.log(url);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        console.log(url);
        var url = "//" + location.host + "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'login') {
        console.log(JSON.stringify(data));
        return;
        var roleid = data.roleid;
        var srvid = data.srvid;
        console.log(url);
        var url = "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {};
pf.prototype.isOpenShare = function () {
    if (SDK_AREA51.flags.share_flag) {
        this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
    }
};

pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function () {
    console.log("shareShare");
    var that = this;
    console.log(SDK_AREA51.flags.share_flag);
    if (SDK_AREA51.flags.share_flag) { //先判断接口状态
        console.log(1);
        var shareParams = {
            title: that.shareInfo.title,
            desc: that.shareInfo.desc,
            link: {
                ext: '', //不可包含中文字符
            },
            imgUrl: that.shareInfo.imgUrl,
            success: function () {
                that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                console.log('‘分享成功回调’');
            }
        }
        SDK_AREA51.share(shareParams); //配置分享接口参数,参数见下方说明
        // alert(SDK_AREA51.share(shareParams));
    }
    //  else {
    //     console.log(2);
    //     this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, false);
    //     //不可进行分享调用，需要隐藏分享按钮
    // }

};
