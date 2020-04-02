var h5game;
var channel;
var open_id;
var cp_uid;
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
    open_id = this.shareInfo.openId;
    var init_url = 'http://h5sdk.zytxgame.com/index.php/api/focus/whale/' + this.passData.appId;
    that.g2b.getDataXHR(init_url, function(response) {
        loadScript("http://hs.joyh5.com/jssdk.js", function() {
            h5game = new h5Game({
                "game_key": response.d.gamekey,
            });
            console.log('h5game' + h5game);
            cp_uid = response.d.cp_uid;
            this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
        });
    });

}

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    console.log(" goodsName " + orderData.subject);
    var param = {};
    var that = this;
    param.openId = orderData.openId; // 2460 用户id
    param.openKey = orderData.openKey; // 2460 验证key
    param.appId = this.passData.appId; // 2460 游戏id
    param.money = amount; // 钱 单位分
    param.orderNo = orderData.orderNo; // 研发游戏订单id
    param.ext = orderData.ext || ""; // serverid
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid; // 商品名
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;

        var real_money = amount / 100;
        // var real_money = 0.01; //  for test
        // var sign = hex_md5(generate_order_id+real_money+param.goodsName+channel+param.openKey);
        // +"sign=" + sign
        // alert("url" + url);

        var pay_t = Date.parse(new Date())
        var pay_time = pay_t / 1000;
        console.log(pay_time);
        var init_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/whale/' + param.appId + '?time=' +
            pay_time + '&order_amount=' + real_money + '&cp_order_id=' + generate_order_id + '&product_name=' +
            param.goodsName + '&notify_url=' + 'http://h5sdk.zytxgame.com/index.php/api/notify/whale/1066' + '&openid=' + open_id;
        that.g2b.getDataXHR(init_url, function(response) {
            if (response.c == 0) {
                console.log('whale init p get ');
                console.log(response.d.game_key);

                var payInfo = {
                    "game_key": response.d.game_key, // 游戏Key
                    "user_uuid": response.d.uuid, // 用户UUID
                    "order_amount": real_money, // 订单金额
                    "cp_order_id": generate_order_id, // CP方的订单ID
                    "product_name": param.goodsName, // 商品名称
                    "notify_url": 'http://h5sdk.zytxgame.com/index.php/api/notify/whale/1066', // 支付完成以后通知CP服务端支付结果的地址
                    "timestamp": pay_time, // 时间戳
                    "signature": response.d.sign // 签名
                }
                h5game.pay(payInfo, function(result) {
                    // alert("status = " + status + " " + "data=" + JSON.stringify(data));
                    if (result.code == 0) {
                        console.log('in result');
                        var order_url = 'http://h5sdk.zytxgame.com/index.php/api/get_order_id/whale/' + param.appId + '?cp_order_id' + generate_order_id + '&money=' + real_money;


                    }
                });
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
        h5game.userCreate({
            "user_uuid": cp_uid, // 用户UUID
            "role": roleid, // 游戏角色的唯一ID
            "nick_name": data.rolename, // 游戏中角色的昵称
            "area": srvid, // 游戏区标志
            "group": srvid // 游戏服务器标志
        });


        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/whale/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
