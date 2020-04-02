var nick_name;
var isAndroid;
var ios_or_android;
var isiOS;
var u;
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
	param.orderNo = ios_or_android;
	// param.orderNo = orderData.orderNo;
	param.ext = orderData.ext || "";
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
	param.platform = 'zhuoyue';
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		// alert("res " + res.d);
		var generate_order_id = res.d.order_id;
		var user_id = res.d.userId;
		var readl_money = amount / 100;
		// var readl_money = 0.01;
		var subject = orderData.subject;
		var iosPayid;
		switch (readl_money) {
			case 8:
				iosPayid = 70001;
				break;
			case 18:
				iosPayid = 70002;
				break;
			case 50:
				iosPayid = 70003;
				break;
			case 98:
				iosPayid = 70004;
				break;
			case 198:
				iosPayid = 70005;
				break;
			case 488:
				iosPayid = 70006;
				break;
			case 988:
				iosPayid = 70007;
				break;
			case 1998:
				iosPayid = 70008;
				break;
			case 28:
				iosPayid = 70009;
				break;
			default:
				iosPayid = 0;

		}
		console.log(param.ext);
		loadScript("http://hsdk.joy8899.com/js/aksdk.js", function() {
			var payInfo = {
				"cpbill": generate_order_id, // 用于游戏方存放订单号
				"productid": "1", // 商品标识
				"productname": subject, // 商品名
				"productdesc": subject, // 商品说明
				"serverid": param.ext, // 服务器编号,字符串类型
				"servername": orderData.ext, // 服务器名字
				"roleid": orderData.actor_id, // 角色id
				"rolename": nick_name, // 角色名
				"rolelevel": 1, // 角色等级,int 类型
				"price": readl_money, // 价格(元)(float 类型)
				"extension": iosPayid
			};
			// alert(JSON.stringify(payInfo));
			AKSDK.pay(payInfo, function(status, data) {
				// alert('status = ' + status);
				var notify = "http://h5sdk.zytxgame.com/index.php/api/notify/zhuoyueiosauditservice?cpbill=" + generate_order_id;
				this.g2b.getDataXHR(notify, function() {

				})
				// alert("status = " + status + " " + "data=" + JSON.stringify(data));
			});
			// alert(JSON.stringify(payInfo));
		});

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
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		nick_name = nickName;
		var level = data.rolelevel;
		var t = new Date();
		var time = t.getTime();
		AKSDK.logEnterGame(srvid, srvid, roleid, nickName, level, time);
		var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;


		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var t = new Date();
		var time = t.getTime();
		AKSDK.logCreateRole(srvid, srvid, roleid, nickName, 0, time);

		var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;

		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/zhuoyue/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.logout = function() {
	function logout() {
		AKSDK.logout(function(status, data) {
			document.getElementById('result').innerHTML = "status = " + status + " " + "data=" + JSON.stringify(data);
		});
	}
};
pf.prototype.showQrCode = function() {
	console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
