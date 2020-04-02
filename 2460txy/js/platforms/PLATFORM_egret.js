var p_name = 'egret';
var egret;
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

	// this.g2b.loadScript("http://h5sdk.zytxgame.com/js/nest.min.js", function() {
	// console.log('egret SDK LOADED');
	// nest.easyuser.login();
	// console.log(nest.easyuser.login());

	this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
	// });



}

pf.prototype.pay = function(amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + orderData);
	console.log(" goodsName " + orderData.subject);
	var param = {};
	param.openId = orderData.openId; // 2460 用户id
	param.openKey = orderData.openKey; // 2460 验证key
	param.appId = this.passData.appId; // 2460 游戏id
	param.money = amount; // 钱 单位分
	param.orderNo = orderData.orderNo; // 厌烦订单id
	param.ext = orderData.ext || ""; // serverid
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid; // 商品名
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		console.log(JSON.stringify(res));

		var generate_order_id = res.d.order_id;
		var money = amount / 100;
		// var money = 0.01; //  for test
		var productname = orderData.subject;
		loadScript("http://h5sdk.zytxgame.com/js/nest.min.js", function() {
			var info = {};
			//设置游戏id。如果是通过开放平台接入，请在开放平台游戏信息-》基本信息-》游戏ID 找到。
			info.egretAppId = 91113;
			//设置使用 Nest 版本。请传递2
			info.version = 2;
			//在debug模式下，请求nest接口会有日志输出。建议调试时开启
			info.debug = true;
			nest.easyuser.startup(info, function(data) {
				console.log('egret startup ' + JSON.stringify(data));
				console.log('nest.easyuser.startup SUCCESS');
				if (data.result == 0) {
					//初始化成功，进入游戏
					nest.easyuser.login({}, function(resultInfo) {
						if (resultInfo.result == 0) { //登录成功
							//resultInfo.token //token 获取用户信息时需传递
							console.log('login SUCCESS');
							var token = resultInfo.token;
							var info = {};
							//购买物品id，在开放平台配置的物品id
							info.goodsId = money;
							//购买数量，当前默认传1，暂不支持其他值
							info.goodsNumber = "1";
							//所在服
							info.serverId = orderData.ext;
							//透传参数
							info.ext = generate_order_id;
							nest.iap.pay(info, function(data) {
								if (data.result == 0) {
									//支付成功
									console.log('pay success');
								} else if (data.result == -1) {
									//支付取消
									console.log('pay cancel');
								} else if (data.result == -3) { //平台登陆账号被踢掉，需要重新登陆
								} else {
									//支付失败
									console.log('pay defined');
								}
							})
						} else if (resultInfo.result == -3) { //平台登陆账号被踢掉，需要重新登陆
							console.log('logined');
							return false;
						} else { //登录失败
							console.log('login defined');
							return false;
						}
					})
				} else {
					//初始化失败，可能是url地址有问题，请联系官方解决
					console.log('nest.easyuser.startup FLASE');
				}
			})
			closePayWindow();
		})


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

		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/egret/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
