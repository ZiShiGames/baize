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
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey; // app key
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
    param.platform = 'allu';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var game_id = param.appId;
        var _input_charset = 'UTF-8';
        var sign;
        var out_trade_no = res.d.order_id;
        var total = param.money;
        // var total = 1; //for test
        var subject = orderData.subject;
        var reserved; //token

        var sign_order_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/wifi/' + param.appId + '?game_id=lcby&open_id=' +
            userId + '&_input_charset=' + encodeURIComponent(_input_charset) + '&out_trade_no=' + out_trade_no + '&total_fee=' + total +
            '&subject=' + 　encodeURIComponent(subject) + '&reserved=' + reserved + '&sign_type=md5';
        this.g2b.getDataXHR(sign_order_url, function(response) {
            reserved = response.d.reserved;
            sign = response.d.ture_sign;
            console.log(sign);
            var pay_url = response.d.pay_url;
            console.log(pay_url);
            window.top.location = pay_url;
        });
        closePayWindow();
    });
};
pf.prototype.checkFocus = function(data) {

    var url = "http://h5sdk.zytxgame.com/index.php/api/focus/allu?openid=" + data.openId;
    this.g2b.getDataXHR(url, function(response) {
        if (response.c == 0) {
            this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, response.d);
        } else {
            this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
        }
    });

};
pf.prototype.reportData = function(data) {
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/wifi/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
    window.top.postMessage({
        cmd: "showFocus"
    }, "*");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};

pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
