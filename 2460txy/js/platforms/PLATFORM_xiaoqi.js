var u;
var hgame;
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    p_name = passData.passId;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
    var that = this;
    this.g2b.loadScript("https://www.x7sy.com/loadx7sdk/x7js_sdk_v20180226.js", function() {
        hgame = new xqhGame();
        console.log(' init in');
        this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
    });


};

pf.prototype.pay = function(amount, orderData) {
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    // param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
    param.platform = passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var user_id = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1 / 100;
        var subject = orderData.subject;
        this.g2b.getDataXHR("http://"+location.host+"/index.php/api/sign_order/" + passId + "/" + param.appId + "?amount=" + readl_money + '&server=' + param.ext + '&uid=' + user_id + '&order_id=' + generate_order_id + '&skey=' + param.ext + '&subject=' + param.goodsName, function(response) {
            if (response.c == 0) {
                var pay_json = {
                    description: '',
                    game_area: param.ext,
                    game_group: '',
                    game_level: '',
                    game_key: response.d.gamekey,
                    game_orderid: generate_order_id,
                    extends_data: generate_order_id,
                    game_price: readl_money,
                    game_role_id: '',
                    stime: response.d.time,
                    subject: param.goodsName,
                    user_id: user_id,
                    pay_sign: response.d.sign,
                    notify_id:-1
                };
                hgame.pay({
                    "pay_obj": JSON.parse(response.d.pay_obj),
                    "complete": function(result) {
                    }
                });
                closePayWindow();
            }
        })

    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//"+location.host+"/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        // var nickName = encodeURIComponent(data.rolename);
        var nickName = data.rolename;
        var cproleid = data.cproleid;
        var url = "//"+location.host+"/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var url = "//"+location.host+"/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function() {
    // alert('click share');
    var that = this;
    hgame.h5game_share({
        game_logo: that.shareInfo.imgUrl,
        show_name: that.shareInfo.title,
        one_game_info: that.shareInfo.desc,
        complete: function(result) {
            /************************************************************
            	返回的result是一个对象
            	errorno 	//0表示分享成功，-1表示分享失败，-2表示取消分享
            	errormsg	//错误描述
            ************************************************************/
            if (result.errorno == 0) {
                // $("#share_result_msg").html("返回的分享结果：" + result.errormsg + "！");
                that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            } else if (result.errorno == -2) {
                // $("#share_result_msg").html("返回的分享结果：" + result.errormsg + "！");
            } else {
                // $("#share_result_msg").html("返回的分享结果：" + result.errormsg + "！");
            }
            // xqhUtil.alert_msg("状态码：" + result.errorno + "，状态信息：" + result.errormsg);
        }
    });


}
