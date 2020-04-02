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
        var pay_type_url = "http://h5sdk.zytxgame.com/index.php/api/init/yybsdk/1071"  + "?uid=" + param.openId+"&server_id="+param.ext;


        this.g2b.getDataXHR(pay_type_url,function(response_pay){
            var pay_type = response_pay.d.pay_type;
            if(pay_type == 'aibei'){

                var real_money = amount/100; // 爱贝 pay
                this.g2b.loadScript("http://h5.xileyougame.com/js/iframe.js", function() {
                    console.log('sdk init');
                    var url = "http://h5sdk.zytxgame.com/index.php/api/focus/yybsdk/1071"  + "?order_id=" + generate_order_id +
                        "&money=" + real_money + "&openId=" + orderData.openId + "&userId=" + res.d.userId + "&goodsName=" + encodeURIComponent(param.goodsName);
                        console.log(url);
                    this.g2b.getDataXHR(url, function(response) {
                        if (response.c == 0) {
                            var url = response.d;

                            iframePay.open(url, function() {
                                console.log("onclose");
                            });
                        }
                    });

                })

            }else if(pay_type=='yyb'){
                var real_money = amount / 10;
                myOwnBri.javapay(generate_order_id, real_money);
            }else{
                alert('debug pay_type is '+pay_type);
            }

        });
        // var real_money = amount / 10;
        // myOwnBri.javapay(generate_order_id, real_money);


        // console.log("myOwnBri " + myOwnBri + " generate_order_id " + generate_order_id + " real_money " + real_money);







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

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/yybsdk/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
