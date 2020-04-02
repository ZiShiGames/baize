var g_g2b;
var is_inited = false;
var open_id;
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
    var hlmy_gw = "";
    var appKey = "";
    var that = this;
    open_id = this.pf_params.open_id;
    this.g2b.loadScript("//passport.4177.com/game/h5sdk", function() {
        var initdata = {
            app_id: that.pf_params.appId,
            open_id: that.pf_params.open_id,
            channel: that.pf_params.channel
        };
        aiaiusdk.init(initdata);

        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
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

        var real_money = amount / 10;
        // var real_money = 1; //  for test

        var goodsName = orderData.subject;
        var roleName = orderData.appUserName;
        var callBackInfo = generate_order_id;


        //this.g2b.loadScript("http://passport.4177.com/game/h5sdk", function() {


        var url = '//' + location.host + '/index.php/api/sign_order/four/' + param.appId + '?openid=' + orderData.openId + "&bill_no=" + generate_order_id + "&goods_name=" + goodsName + "&total_fee=" + real_money + "&ext=" + generate_order_id;

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                //console.log(" paySafecode : ok");

                var paydata = {
                    "open_id": response.d.open_id,
                    "access_token": response.d.access_token,
                    "bill_no": generate_order_id,
                    "goods_name": goodsName,
                    "total_fee": real_money,
                    "ext": generate_order_id,
                    "sign": response.d.sign,
                };
                aiaiusdk.pay(paydata, function(code, msg) {});
            } else {}
        });


        //});


        closePayWindow();

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
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.loadScript("//passport.4177.com/game/h5sdk", function() {
            this.g2b.getDataXHR(url, function(response) {
                if (response.c == 0) {
                    console.log("login_initdata : " + response.d.app_id);
                    console.log("login_info : " + response.d.p_uid);
                    var initdata = {
                        "app_id": response.d.app_id,
                        "open_id": response.d.open_id,
                        "channel": response.d.channel,
                    };
                    aiaiusdk.init(initdata);

                    var serverData = {
                        open_id: response.d.p_uid,
                        server_id: srvid,
                        server_name: srvid
                    };
                    aiaiusdk.selectServer(serverData);
                    open_id = response.d.p_uid;

                }
            });
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.loadScript("//passport.4177.com/game/h5sdk", function() {
            this.g2b.getDataXHR(url, function(response) {
                if (response.c == 0) {
                    console.log("create_role_info : " + response.d.p_uid);
                    var roleData = {
                        open_id: response.d.p_uid,
                        server_id: srvid,
                        server_name: srvid,
                        role_id: roleid,
                        role_name: data.rolename
                    };
                    aiaiusdk.createRole(roleData);
                    open_id = response.d.p_uid;

                }
            });
        });
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/four/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};


pf.prototype.showShare = function() {
    // alert(open_id);
    var that = this;
    var shareData = {
        open_id: open_id
    };
    aiaiusdk.share(shareData, function(code, msg, data) {
        // alert('1');
        // console.log('1');
        // alert(code);
        if (code == 101) {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        } else {

        }
    });
}

pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
