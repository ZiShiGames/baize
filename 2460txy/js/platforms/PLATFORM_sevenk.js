var p_name;
var p_userid;
var p_appkey;
// var K7_SDK;
var sdkInit;
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
    var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "/" + this.passData.appId;
    this.g2b.getDataXHR(url, function (response) {
        if (response.c == 0) {
            console.log(JSON.stringify(response.d));
            sdkInit = {
                "account": response.d.account,
                "appkey": response.d.appkey,
                "k7_vaildCode": response.d.k7_vaildCode,
            }
            p_appkey = response.d.appkey;
            p_userid = response.d.account;
            console.log(JSON.stringify(sdkInit));
            this.g2b.loadScript("//h5.7k7k.com/assets/v0.1.0/k7sdk.js", function () {
                console.log('sdk in ');
                this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
            });
        }
    });

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
        var goodsid = readl_money;

        // var readl_money = 0.01; // for test

        var url = "//" + location.host + "/index.php/api/sign_order/sevenk/" + param.appId + "?order_id=" + generate_order_id +
            "&money=" + readl_money + "&userId=" + p_userid + "&ext=" + ext;

        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                K7_SDK.Pay({
                    "safeCode": response.d.safecode
                })
                console.log(response.d.safecode);
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
        K7_SDK.RoleInfo({
            "serverid": srvid,
            "servername": srvid + "区",
            "roleid": 1,
            "rolename": nickName,
            "rolelevel": level,
            "appkey": p_appkey,
            "account": p_userid
        });
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

        var url = "//" + location.host + "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.showQrCode = function () {};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};

pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
pf.prototype.showShare = function () {
    var that = this;
    K7_SDK.wxShare({
      title: that.shareInfo.title,
      desc: that.shareInfo.desc,
      custom:that.shareInfo.desc,
      imgUrl: that.shareInfo.imgUrl,
      isUseGuide: 'yes'
     })
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
};
function wxShareRes() {
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}

// function wxShareRes() {
// 	//游戏在方法内实现自身逻辑，如：提示用户分享成功，并发放奖励
// 	console.log('test share');
// 	this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
// };
