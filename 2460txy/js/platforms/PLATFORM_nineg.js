var game9g;

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
    that.g2b.loadScript("https://game.9g.com/js/lib.v2.js", function() {
        // console.log("that.pf_params " + JSON.stringify(that.pf_params));
        game9g = new Game9G({
            gameid: that.pf_params['gameid'],
            channel: that.pf_params['channel'],
            token: that.pf_params['token']
        });

        // game9g.share({
        //     title: "龙城霸业",
        //     content: "百万元宝悬赏，邀兄弟共战龙城" // 仅发送朋友或群显示，发送朋友圈不显示
        // });

        game9g.onShareOK(function() {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        });
        game9g.ready(function(data) {
            console.log((data));
        });
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
    });
    // that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function(amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    var that = this;
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.pfid = orderData.pfid || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {

        var generate_order_id = res.d.order_id;
        var real_money = amount;
        // var real_money = 1; //  for test

        var url = "//" + location.host + "/index.php/api/sign_order/nineg/" + param.appId + "?orderid=" + generate_order_id + "&money=" + real_money;
        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {

                var data = {
                    orderid: generate_order_id,
                    money: real_money,
                    product: orderData.subject,
                    spid: that.pf_params['gameid'],
                    sign: response.d
                }

                game9g.pay(data);
            }
        });
        closePayWindow();


    });
};
pf.prototype.checkFocus = function(data) {
    game9g.checkSubscribe(function(result) {
        console.log("result " + result);
        this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, result);
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
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
        var server = {
            server_id: srvid,
            server_name: srvid
        };
        game9g.selectServer(srvid);

    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});


        var role = {
            server_id: srvid,
            server_name: srvid,
            role_id: roleid,
            nickname: nickName
        };
        console.log('create role ok');
        game9g.createRole(role);
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/nineg/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    game9g.gotoSubscribe();
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log("shareShare");
  var that = this;
    // game9g.share({
    //     title: "中华铁路",
    //     content: "中国首款火车策略经营游戏，路程再远也要回家，从家乡开始畅游全世界。" // 仅发送朋友或群显示，发送朋友圈不显示
    // });
    game9g.share({
        title: that.pf.shareInfo.title,
        content: that.pf.shareInfo.desc, // 仅发送朋友或群显示，发送朋友圈不显示
    });
}
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
