var p_name = 'mtyx';

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

    var that = this;
    that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);

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
        // var readl_money = 1;
        var gameid;
        var focus_url = "http://h5sdk.zytxgame.com/index.php/api/focus/mtyx/" + param.appId;
        this.g2b.getDataXHR(focus_url, function(response) {
            if (response.c == 0) {
                gameid = response.d.game_id;
                var data = {
                    protocol: 100601,
                    subject: orderData.subject,
                    total_fee: readl_money,
                    uid: userId,
                    game_id: gameid,
                    server_id: orderData.ext,
                    notify_url: "http://h5sdk.zytxgame.com/index.php/api/notify/mtyx",
                    callback_url: "http://h5.xileyougame.com",
                    trade_no: generate_order_id
                };
                console.log(response.d.game_id);

                var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/mtyx/" + param.appId + "?p=" + encodeURIComponent(JSON.stringify(data));
                this.g2b.getDataXHR(url, function(response) {
                    if (response.c == 0) {
                        var order_data = response.d;

                        quwanwansdk.pay({
                            token: order_data.token,
                            sign: order_data.sign,
                            callFunc: function(status, msg) {
                                if (status == "success") {
                                    console.log("支付成功");
                                } else {
                                    console.log("支付失败：" + msg);
                                }
                            }
                        });
                    }
                });
            } else {
                log_message('error', p_name + ' api->focus error , response.c = ' + response.c);
            }
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

pf.prototype.showShare = function() {
    console.log("shareShare");
    var that = this;

    quwanwansdk.change_share_info({
        title: "龙城霸业",
        summary: "百万元宝悬赏，邀兄弟共战龙城",
        img_url: "http://h5.xileyougame.com/img/icon/751.png",
        callFunc: function(status) {
            if (status == "success") {
                console.log("分享成功");
                that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            } else {
                console.log("分享取消");
            }
        },
        show_share: true
    });
}
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
