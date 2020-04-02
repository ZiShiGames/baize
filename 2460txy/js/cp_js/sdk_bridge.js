//说明 游戏登录sdk
var sdklogin = window.sdklogin = this.sdklogin = {
	
	/**
	 * 天宇游戏登录回调 只要回调了就是重新登录一个新的帐号
	 * @param {String} _token 登录口令
	 * @param {Int} _mem_id 用户id
	 * @param {Int} _game_id 游戏id
	 */
	tianyuyou:function(_token,_mem_id,_game_id){
		//登录操作TODO
		var result = true; //登录情况
		if(result){
			//做登录操作成功
			if(typeof tyyconfiglogin != 'undefined'){
				tyyconfiglogin.tianyuyou(_token,_mem_id,_game_id);
			}else{
				alert('请配置登录后操作');
			}
			return;
		}else{
			//失败返回
			tyysdk.changeaccount();
			return;
		}
	},
	
	/**
	 * 支付回调，不能用于真实回调，以服务器通知为准
	 * @param {Boolean} _result 成功失败
	 * @param {String} _string cp attach参数或者错误信息
	 */
	payorder:function(_result,_string){
		if(_result){ //支付成功
			//TODO 支付成功操作
			tyysdk.toast('支付成功');
		}else{ //支付失败
			tyysdk.toast(_string+'');
		}
	},
	
	/**
	 * 登录
	 * @param {Object} _function 回调函数 可以为空 默认回调这里的tianyuyou
	 */
	login:function(_function){
		tyysdk.login(_function);
	},
	
	/**
	 * 退出登录
	 * @param {Object} _serverid 服务器id
	 * @param {Object} _function 回调函数 第一个参数BOOLean 成功失败
	 */
	loginout:function(_serverid,_function){
		tyysdk.loginout(_function);
	},
	
	/**
	 * 切换帐号
	 * @param {Object} _function 回调函数 可以为空 默认回调这里的tianyuyou
	 */
	changeaccount:function(_function){
		tyysdk.changeaccount(_function);
	},
	
	/**
	 * 调起订单支付 order_no从服务端获取 回调这里的payorder
	 * @param {String} _order_no
	 */
	callpay:function(_order_no){
		tyysdk.payorder(_order_no);
	},
	
	/**
	 * 初始化
	 * @param {Boolean} _flashscreen 是否显示闪屏
	 * @param {Function} _function 回调函数 回调第一个参数Boolean(成功失败) 第二个参数string(文字信息)
	 */
	init:function(_flashscreen,_function){
		tyysdk.init(_flashscreen,_function);
	},
	
	/**
	 * 返回用户id
	 */
	mem_id:function(){
		return tyysdk.mem_id;
	},
	
	/**
	 * 用户token
	 */
	token:function(){
		return tyysdk.token;
	},
	
	/**
	 * 游戏id
	 */
	game_id:function(){
		return tyyconfig.configs().app_id;
	},
	
	/**
	 * 全享接口
	 * @return 返回分享对象 icon 图片  title标题 url 分享url
	 */
	shareinfo:function(){
		return tyyconfig.configs().shareinfo;
	}
	
}
