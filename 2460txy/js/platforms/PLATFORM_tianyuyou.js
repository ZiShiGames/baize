var g_g2b;
var is_inited = false;
var open_id;
var cpAppid = '';
var cpMem_id = '';
var cpUser_token = '';
var cpRoleid = '';
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    g_g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
    var that = this;
    cpAppid = this.pf_params.app_id;
    cpMem_id = this.pf_params.mem_id;
    cpUser_token = this.pf_params.token;
    this.g2b.loadScript('http://cdn2.tianyuyou.cn/resource/h5sdk/v1/js/tyysdk.js', function() {});
    this.g2b.loadScript("http://api.tianyuyou.cn/h5sdk/sdkconfig/" + cpAppid + ".js?v=" + new Date().getTime(), function() {
        that.g2b.loadScript("http://h5sdk.cdn.zytxgame.com/js/cp_js/sdk_bridge.js", function() {
            sdklogin.init(false, function() {
                console.log('cp init ok');
            });
            that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
        });
    });
}

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    console.log(" goodsName " + orderData.subject);
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
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;

        var real_money = amount / 100;
        // var real_money = 1; //  for test

        var goodsName = orderData.subject;
        var roleName = orderData.appUserName;
        var callBackInfo = generate_order_id;
        var url = '//' + location.host + '/index.php/api/sign_order/tianyuyou/' + param.appId + '?app_id=' + cpAppid + "&mem_id=" + cpMem_id + "&token=" + cpUser_token + "&income_amount=" + real_money +
            "&roleid=" + cpRoleid +
            "&serverid=" + param.ext +
            "&productname=" + goodsName +
            "&attach=" + generate_order_id;

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var cp_order_id = response.d.order_id;
                var mem_id = response.d.mem_id;
                var income_amount = response.d.income_amount;
                console.log(cp_order_id);
                sdklogin.callpay(cp_order_id);
            } else {}
        });


        closePayWindow();

    });
};

pf.prototype.checkFocus = function(data) {

};

pf.prototype.reportData = function(data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        cpRoleid = roleid;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
        var uploadUrl = '//' + location.host + '/index.php/api/focus/tianyuyou/' + this.passData.appId + '?app_id=' + cpAppid + "&mem_id=" + cpMem_id + "&token=" + cpUser_token + "&rolename=" + data.rolename +
            "&roleid=" + cpRoleid +
            "&zoneid=" + srvid +
            "&rolelevel=" + level +
            "&zonename=" + srvid;
        this.g2b.getDataXHR(uploadUrl, function(response) {});

    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/tianyuyou/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};


pf.prototype.showShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, false);
}

pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);

};
