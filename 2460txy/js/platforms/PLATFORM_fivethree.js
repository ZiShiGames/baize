var cpNickname;
var cpLevel;
var cpRoleid;
var cpSdkloginmodel;
var cpSrvid;
var cpUserId;
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
    console.log("init done");
    cpSdkloginmodel = this.pf_params.sdkloginmodel;
    cpUserId = this.pf_params.user_id;

    var that = this;
    var time = new Date().getTime();
    var url = "http://www.53fun.com/Public/static/xigusdk/xgh5sdk.js?" + time;
    this.g2b.loadScript(url, function() {
        that.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
    });
};

pf.prototype.pay = function(amount, orderData) {
    // console.log("amount " + amount);
    // console.log("orderData " + JSON.stringify(orderData));
    var that = this;
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
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay(
        {
            search: search
        },
        function(res) {
            // console.log(JSON.stringify(res));
            var generate_order_id = res.d.order_id;
            var user_id = res.d.userId;
            var readl_money = amount; // for test
            var props_name = param.goodsName;
            var channelExt = generate_order_id;
            var trade_no = generate_order_id;

            var url =
                "//" +
                location.host +
                "/index.php/api/sign_order/" +
                that.passData.passId +
                "/" +
                that.passData.appId +
                "?amount=" +
                readl_money +
                "&channelExt=" +
                channelExt +
                "&props_name=" +
                props_name +
                "&trade_no=" +
                trade_no +
                "&user_id=" +
                user_id +
                "&sdkloginmodel=" +
                cpSdkloginmodel;

            this.g2b.getDataXHR(url, function(response) {
                if (response.c == 0) {
                    var amount = response.d.amount;
                    var channelExt = response.d.channelExt;
                    var game_appid = response.d.game_appid;
                    var props_name = response.d.props_name;
                    var trade_no = response.d.trade_no;
                    var user_id = response.d.user_id;
                    var sdkloginmodel = response.d.sdkloginmodel;
                    var sign = response.d.sign;

                    var jsondata = {
                        amount: amount,
                        channelExt: channelExt,
                        game_appid: game_appid,
                        props_name: props_name,
                        trade_no: trade_no,
                        user_id: user_id,
                        sdkloginmodel: sdkloginmodel,
                        sign: sign,
                        server_id: cpSrvid,
                        server_name: "",
                        role_id: cpRoleid,
                        role_name: cpNickname
                    };
                    xgGame.h5paySdk(jsondata, function(data) {
                        console.log(data);
                    });
                }
            });
        }
    );
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, 0);
};
pf.prototype.reportData = function(data) {
    console.log(JSON.stringify(data.action));
    if (data.action == "enterGame") {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url =
            "//" +
            location.host +
            "/index.php/api/login/" +
            this.passData.passId +
            "/" +
            this.passData.appId +
            "?roleid=" +
            roleid +
            "&srvid=" +
            srvid +
            "&nickname=" +
            nickName +
            "&level=" +
            level +
            "&power=" +
            power +
            "&currency=" +
            currency +
            "&cproleid=" +
            cproleid;
        cpLevel = level;
        cpNickname = data.rolename;
        cpRoleid = cproleid;
        cpSrvid = srvid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });

        // 进入游戏上报接口, 给渠道
        var _url =
            "//" +
            location.host +
            "/index.php/api/focus/" +
            this.passData.passId +
            "/" +
            this.passData.appId +
            "?roleid=" +
            roleid +
            "&srvid=" +
            srvid +
            "&nickname=" +
            nickName +
            "&level=" +
            level +
            "&cproleid=" +
            cproleid;
        this.g2b.getDataXHR(_url, function(response) {
            console.log(JSON.stringify(response.m));
            var game_appid = response.d.game_appid;
            var sign = response.d.sign;
            var jsondata = {
                user_id: cpUserId,
                game_appid: game_appid,
                server_id: srvid,
                server_name: srvid,
                role_id: cproleid,
                role_name: cpNickname,
                level: level,
                sign: sign
            };
            xgGame.jointCreateRole(jsondata);
        });
    } else if (data.action == "create_role") {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url =
            "//" +
            location.host +
            "/index.php/api/create_role/" +
            this.passData.passId +
            "/" +
            this.passData.appId +
            "?roleid=" +
            roleid +
            "&srvid=" +
            srvid +
            "&nickname=" +
            nickName +
            "&cproleid=" +
            cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == "enterCreate") {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url =
            "//" +
            location.host +
            "/index.php/api/sign_collect/" +
            this.passData.passId +
            "/" +
            this.passData.appId +
            "?roleid=" +
            roleid +
            "&srvid=" +
            srvid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    var that = this;
    // document.getElementById("qr_modal").style.display = "block";
    xgGame.follow(
        {
            game_appid: that.pf_params.game_appid
        },
        function(data) {
            Console, log(data);
        }
    );
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log("click share button");
    var that = this;
    xgGame.shareTips(
        {
            game_appid: that.pf_params.game_appid
        },
        function(data) {
            Console, log(data);
        }
    );

    xgGame.shareSdk(
        {
            game_appid: that.pf_params.game_appid,
            title: that.shareInfo.title,
            desc: that.shareInfo.desc
        },
        function(data) {
            //分享结果status  1分享成功   0分享失败
            console.log(data);
            if (data.status == 1) {
                that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            }
        }
    );
};
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
