// var cp_appid;
// var cp_time = 0;

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
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
    // cp_appid = this.pf_params.appId;
}

pf.prototype.pay = function (amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData, typeof (orderData));
    console.log(" goodsName " + orderData.subject);
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
    param.cproleid = orderData.cproleid;
    param.platform = this.passData.passId;

    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function (res) {
        var generate_order_id = res.d.order_id;

        var real_money = amount / 100;
        // var real_money = 1; //  for test

        var goodsName = orderData.subject;
        var roleName = orderData.appUserName;
        var callBackInfo = generate_order_id;
        var userId = res.d.userId;

        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId + "?money=" + real_money + "&order_no=" + generate_order_id + "&server_id=" + orderData.ext + "&userId=" + userId + "&goods_name=" + goodsName;

        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                console.log(" payinfo : " + response.d);
                window.parent.postMessage(response.d, "*");
            }
        });

        closePayWindow();

    });
};

pf.prototype.checkFocus = function (data) {
    console.log(JSON.stringify(data));
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

        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
    alert('请分享至微信好友');
};
pf.prototype.showShare = function () {
    console.log('click share button');
    alert('请分享至微信好友');
};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
window.addEventListener("message", function (e) {
    console.log(JSON.stringify(e));
    console.log('data:' + e.data);
    var that = this;
    if (e.data == 'shareok') {
        // cp_time = cp_time + 1;
        // if (cp_appid == 1483) {
        //     console.log("恭喜你，分享成功! " + cp_time);
        // }
        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    }
});
