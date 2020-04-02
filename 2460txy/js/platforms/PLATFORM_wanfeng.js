var cpNickname;
var cpLevel;
var cpGameId;
var cpOpenId;
var wfGame1;
var pf = function (g2b, shareInfo, pf_params, passData) {
	this.g2b = g2b;
	this.shareInfo = shareInfo;
	this.pf_params = pf_params;

	this.passData = passData;
	this.reyunurl = "";
	this.init();
};
pf.prototype = new platform();

pf.prototype.init = function () {
	console.log('init done');

	// cpUid = this.pf_params.uid;

	var _time = new Date().getTime();
	var that = this;

	this.g2b.loadScript('//wap.beeplay123.com/loadsdk/index.js?time=' + _time, function () {

		// 初始化
		wfGame1 = wfGame();

		var url = "//" + location.host + "/index.php/api/focus/" + that.passData.passId + "/" + that.passData.appId;

		this.g2b.getDataXHR(url, function (response) {
			if (response.c == 0) {
				console.log("成功：", response);

				// 添加埋点  登陆成功

				// 存全局变量
				cpGameId = response.d.gameId;
				cpOpenId = response.d.openId;

			}
		});


		console.log("success");
		this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);

	});

};

pf.prototype.pay = function (amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + JSON.stringify(orderData));
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
	this.g2b.createPay({
		search: search
	}, function (res) {
		console.log(JSON.stringify(res));
		var generate_order_id = res.d.order_id; // 我们的订单号 || ext
		var userId = res.d.userId;
		var readl_money = amount / 100; // for test

		readl_money = readl_money + '';

		var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
			"?gameOrderno=" + generate_order_id +
			"&price=" + readl_money +
			"&openId=" + res.d.userId +
			"&gameArea=" + param.ext +
			"&gameProp=" + param.goodsName +
			'&gameRoleId=' + param.cproleid;

		this.g2b.getDataXHR(url, function (response) {
			if (response.c == 0) {
				console.log("成功：", response);
				cpLevel = cpLevel + '';

				wfGame1.pay({
					"gameId": response.d.gameId,
					"openId": cpOpenId,
					"timestamp": response.d.timestamp, // 创建订单时的时间戳，格式：20180316145800
					"gameOrderno": res.d.order_id, // 贵方订单号。支付成功后，我方向贵方服务器回传该订单号
					"gameProp": response.d.gameProp, // 充值金额（游戏币）
					"price": response.d.price + '', // 充值金额（人民币）
					"gameArea": response.d.gameArea, // 游戏分区
					"gameGroup": response.d.gameArea, // 游戏分服
					"gameLevel": cpLevel, // 角色等级
					"gameRoleId": response.d.gameRoleId, // 角色名称
					"gameNiceName": cpNickname, //用户昵称
					"sign": response.d.sign
				});
			}
		});

	});
};
pf.prototype.checkFocus = function (data) {

	// var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
	// this.g2b.getDataXHR(url, function(response) {
	//     if (response.c == 0) {
	this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
	// } else {
	//     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
	// }
	// });

};
pf.prototype.reportData = function (data) {
	console.log(JSON.stringify(data));
	if (data.action == 'enterGame') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var nickName = encodeURIComponent(data.rolename);
		var level = data.rolelevel;
		var power = data.power;
		var currency = data.currency;
		var proleid = data.cproleid;

		var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

		cpLevel = level;
		cpNickname = data.rolename;

		this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
            var params = {
				"eventId": "3601000002", //事件ID （必填）
				"openId": cpOpenId, //用户ID（必填）
				"gameId": cpGameId, //游戏ID（必填）
				"eventName": "开始游戏", //事件名称（必填）
				"gameRoleId": roleid, //游戏角色ID
				"gameRoleName": cpNickname, //游戏角色名称
				"nickname": cpNickname, //昵称
				"gameArea": srvid, //游戏的区
				"gameGroup": srvid, //游戏的服
				"roleLevel": cpLevel, //角色等级
				"vipLevel": "", //VIP等级
                "balance": "", //账户余额
                "price":"",//购买商品的价格
                "offlineTime":""// 离线时长

            };
            console.log(params);
			// 埋点  开始游戏
            wfGame1.buryingData(params);
            params.eventId = "3600000001";
            params.eventName = "登陆成功";
            wfGame1.buryingData(params);
		});



	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var nickName = encodeURIComponent(data.rolename);
		var cproleid = data.cproleid;
		var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function (response) {
			console.log(JSON.stringify(response));

			// 埋点  开始游戏（创建角色）
			wfGame1.buryingData({
				"eventId": "3600000004", //事件ID （必填）
				"openId": cpOpenId, //用户ID（必填）
				"gameId": cpGameId, //游戏ID（必填）
				"eventName": "开始游戏（创建角色）", //事件名称（必填）
				"gameRoleId": roleid, //游戏角色ID
				"gameRoleName": cpNickname, //游戏角色名称
				"nickname": cpNickname, //昵称
				"gameArea": srvid, //游戏的区
                "gameGroup": srvid, //游戏的服
                "roleLevel": 1, //角色等级
				"vipLevel": "", //VIP等级
                "balance": "", //账户余额
                "price":"",//购买商品的价格
                "offlineTime":""// 离线时长
			});
		});



	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;

		var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function (response) {
			console.log(JSON.stringify(response));
		});
	}
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
	console.log("pf showQrCode called");
	// document.getElementById("qr_modal").style.display = "block";
};
pf.prototype.isOpenShare = function () {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.showShare = function () {
	console.log('click share button');
	this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function () {
	// this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
