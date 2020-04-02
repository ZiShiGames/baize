var p_name;
var cpGameid = '';
var cpUid = '';
var gameId = '';
var pf = function (g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    p_name = passData.passId;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function () {
    cpGameid = this.pf_params.gameid;
    cpUid = this.pf_params.uid;
    gameId = this.pf_params.appId;
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function (amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + orderData);
    var param = {};
    var that = this;
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
    }, function (res) {
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1 / 100; // for test

        var goodsid;
        this.g2b.loadScript("http://res.5151.com/Public/h5/js/wy.js", function () {
            var url = "//" + location.host + "/index.php/api/sign_order/fiveone/1079?uid=" + userId + "&gameid=" + cpGameid;
            // + "?order_id=" + generate_order_id +
            // 	"&money=" + readl_money + "&openId=" + orderData.openId + "&userId=" + userId + "&goodsName=" + encodeURIComponent(param.goodsName);
            console.log('sdk in');
            this.g2b.getDataXHR(url, function (response) {
                console.log(JSON.stringify(response.d));
                if (response.c == 0) {
                    var url = response.d;
                    console.log(gameId);
                    console.log(amount);
                    if (gameId == 1165) {
                        switch (amount) {
                            case 600:
                                goodsid = 3618;
                                break;
                            case 3000:
                                goodsid = 3619;
                                break;
                            case 5000:
                                goodsid = 3620;
                                break;
                            case 12800:
                                goodsid = 3621;
                                break;
                            case 28800:
                                goodsid = 3622;
                                break;
                            default:
                                alert('goodsid is null');
                                break;
                        };
                    } else if (gameId == 1164) {
                        switch (amount) {
                            case 100:
                                console.log('is 100');
                                goodsid = 3623;
                                break;
                            case 1000:
                                goodsid = 3624;
                                break;
                            case 4700:
                                goodsid = 3625;
                                break;
                            case 9000:
                                goodsid = 3626;
                                break;
                            case 25500:
                                goodsid = 3627;
                                break;
                            case 48000:
                                goodsid = 3628;
                                break;
                            case 75000:
                                goodsid = 3629;
                                break;
                            default:
                                alert('goodsid is null');
                                break;
                        };
                    } else if (gameId == 1356) {
                        switch (amount) {
                            case 64800:
                                goodsid = 5074;
                                break;
                            case 28800:
                                goodsid = 4871;
                                break;
                            case 18800:
                                goodsid = 4870;
                                break;
                            case 9800:
                                goodsid = 4869;
                                break;
                            case 6800:
                                goodsid = 4868;
                                break;
                            case 4800:
                                goodsid = 4867;
                                break;
                            case 1800:
                                goodsid = 4866;
                                break;
                            case 2500:
                                goodsid = 4865;
                                break;
                            case 1000:
                                goodsid = 4864;
                                break;
                            case 300000:
                                goodsid = 4854;
                                break;
                            case 250000:
                                goodsid = 4853;
                                break;
                            case 200000:
                                goodsid = 4852;
                                break;
                            case 180000:
                                goodsid = 4851;
                                break;
                            case 150000:
                                goodsid = 4850;
                                break;
                            case 120000:
                                goodsid = 4849;
                                break;
                            case 100000:
                                goodsid = 4848;
                                break;
                            case 80000:
                                goodsid = 4847;
                                break;
                            case 60000:
                                goodsid = 4846;
                                break;
                            case 50000:
                                goodsid = 4845;
                                break;
                            case 40000:
                                goodsid = 4844;
                                break;
                            case 30000:
                                goodsid = 4843;
                                break;
                            case 20000:
                                goodsid = 4842;
                                break;
                            case 10000:
                                goodsid = 4841;
                                break;
                            case 5000:
                                goodsid = 4840;
                                break;
                            case 5000:
                                goodsid = 5073;
                                break;
                            case 3000:
                                goodsid = 4839;
                                break;
                            case 2000:
                                goodsid = 4838;
                                break;
                            case 1000:
                                goodsid = 4837;
                                break;
                            default:
                                alert('goodsid is null');
                                break;
                        };

                    } else if (gameId == 1420) {
                        switch (amount) {
                            case 600:
                                console.log('is 100');
                                goodsid = 6165;
                                break;
                            case 3000:
                                goodsid = 6166;
                                break;
                            case 10000:
                                goodsid = 6167;
                                break;
                            case 50000:
                                goodsid = 6168;
                                break;
                            case 200000:
                                goodsid = 6169;
                                break;
                            default:
                                alert('goodsid is null');
                                break;
                        };
                    }

                    var pay_data = {
                        "action": "pay",
                        "uid": response.d.userId,
                        "gameid": cpGameid,
                        "amount": readl_money,
                        "productid": goodsid,
                        "goodid": goodsid,
                        "ext": generate_order_id,
                    };
                    console.log(JSON.stringify(pay_data));
                    wy.pay(pay_data);
                    console.log("onclose");
                }
            });

        });

        // myOwnBri.startPhone('1');

    });
};
pf.prototype.checkFocus = function (data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function (data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        var that = this;
        that.g2b.getDataXHR("//" + location.host + "/index.php/api/focus/fiveone/1079?serverid=" + srvid + '&nickname=' + nickName + '&rolelv=' + level, function (response) {
            that.g2b.loadScript("http://res.5151.com/Public/h5/js/wy.js", function () {
                if (response.c == 0) {
                    var data = {
                        serverid: response.d.serverid, // 区服id 参与签名
                        rolename: decodeURIComponent(response.d.nickname), // 游戏角色名
                        rolelevel: response.d.rolelv, // 用户等级 参与签名
                        round: 0, //用户转生等级，必须为数字，若无，传入0 参与签名
                        balance: 0, //用户游戏币余额，必须为数字，若无，传入0
                        vip: 0, //当前用户VIP等级，必须为数字，若无，传入0
                        partyname: '无帮派', //当前角色所属帮派，不能为空，不能为null，若无，传入“无帮派”
                        time: response.d.time, // 时间戳 参与签名
                        sign: response.d.sign // 签名
                    };
                    wy.roleinfo(data);
                }
            });
        });

        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};

pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};


pf.prototype.showShare = function () {
    console.log('click share');
    var shareConfig = {};
    shareConfig.callbackFun = function () {
        this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true); //给玩家发奖励
        alert('谢谢分享成功'); //游戏方填写回调函数，不填写为假分享
    }
    lq.share(shareConfig);
}
