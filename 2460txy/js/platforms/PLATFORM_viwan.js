var p_name = 'viwan';
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

}

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    console.log(" goodsName " + orderData.subject);
    var param = {};
    param.openId = orderData.openId; // 2460 用户id
    param.openKey = orderData.openKey; // 2460 验证key
    param.appId = this.passData.appId; // 2460 游戏id
    param.money = amount; // 钱 单位分
    param.orderNo = orderData.orderNo; // 厌烦订单id
    param.ext = orderData.ext || ""; // serverid
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid; // 商品名
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var username = res.d.userId;;
        var productname = param.goodsName;
        var money = amount / 100;
        // var money = 0.01;
        var token;
        var appid;
        var roleid = param.openId;
        var serverid = param.ext ;
        var remarks = res.d.order_id;
        // var real_money = 1; //  for test
        var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/' + p_name + '/' + param.appId +"?username=" + username +
            "&productname=" + productname +
            "&amount=" + money +
            "&serverid=" + serverid +
            "&remarks=" + remarks +
            "&roleid=" + roleid;
        this.g2b.getDataXHR(sign_url, function(response) {
            appid = response.d.appid;
            token = response.d.token;
            var show_url = "http://h5g.1862.cn/sdk.php/User/Pay/subpage?username=" + username + "&productname=" + productname +
                "&amount=" + money + "&appid=" + appid + "&token=" + token + "&roleid=" + roleid + "&serverid=" + serverid + "&remarks=" + remarks;
            show_url = show_url + "&t=" + String(Math.random());
            if (typeof(exec_obj) == 'undefined') {
                exec_obj = document.createElement('iframe');
                exec_obj.name = 'tmp_frame';
                exec_obj.src = show_url;
                exec_obj.style.display = 'none';
                document.body.appendChild(exec_obj);
            } else {
                exec_obj.src = show_url;
                log_message('error', p_name + ' openPay is error, show_url :' + show_url + ' , exec_obj type is : ' + typeof(exec_obj));
            }
        });
        // closePayWindow();

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

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/" + p_name + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function() {};
