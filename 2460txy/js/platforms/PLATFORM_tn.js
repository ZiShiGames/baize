var p_name = 'tn';
var cp_gameid = '';
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
    // if (this.passData.appId == 1183) {
    //     cp_gameid = '345';
    // }else if (this.passData.appId == 1184) {
    //     cp_gameid = '346';
    // }
    cp_gameid = this.pf_params.gameid;
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);

};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
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
    param.platform = p_name;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 0.01;
        var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/' + p_name + '/' + param.appId + '?uid=' +
            userId + '&amount=' + readl_money + '&generate_order_id=' + generate_order_id +
            '&body=' + param.goodsName + '&detail=' + param.goodsName + '&out_attach=' +
            generate_order_id;

        this.g2b.getDataXHR(sign_url, function(response) {
            if (response.c == 0) {
                var o = {
                    uid: userId,
                    amount: readl_money,
                    out_order_id: generate_order_id,
                    body: param.goodsName,
                    detail: param.goodsName,
                    gameid: cp_gameid,
                    out_attach: generate_order_id,
                    // time: response.d.time,
                    // sign: response.d.sign,
                };
                var j = {
                    uid: userId,
                    amount: readl_money,
                    out_order_id: generate_order_id,
                    body: param.goodsName,
                    detail: param.goodsName,
                    gameid: cp_gameid,
                    out_attach: generate_order_id,
                    time: response.d.time,
                    sign: response.d.sign,
                };

                console.log(JSON.stringify(j));
                window.parent.postMessage(JSON.stringify(j), '*');

            }

        });



    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);

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
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};


pf.prototype.showShare = function() {

    var obj = {
        type: "f",
        name: "fx",
        title: "标题"
    }
    window.parent.postMessage(JSON.stringify(obj), '*');
    window.addEventListener("message", function(e) {
        var o = JSON.parse(e.data);
        if (o.type == "f" && o.name == "fx" && o.issuccess == true) {
            //这里判断分享成功
            this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
        }
    });




    console.log("shareShare");
};
