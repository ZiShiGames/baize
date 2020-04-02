<?php
$config = array (
		//应用ID,您的APPID。
		//'app_id' => "2016123004733365",
        'app_id' => "2019072265954249",
		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEAqFJ9S63v4E5z6ANLJeVa15IHK7csMsP0q43o9nWZXqsHD4WtcQ747WQVbksFr2zL1d2oB6gmye3FukuJS8E6/+OJEIHx+1D8y4RviT4hXjadxj1p8vOEYLv/UVrJ6g06FoG99IfzFW7Fnyd7rP5vRtPXVJ0izT5fw1/mAe2JCHfxDs4NDxaIEtyEpuYLdGq8/qgq+yEAyKdDuvcSbOi6yiaebXu0FFR/TDpEU30ZW9PLVq1ebqs4E/zvegAQFfwu+CSImClMrsYAI3pZ5Ak2weyiN0a06G2kHNEQPxML9mitPLwFaRePUkg+jnVTb7KOgi70lnu2aBKDkk1w2PiyNQIDAQABAoIBAAM/vF1mmRe6S/Bxh9TObYd1sR+95GOCdsmM7q87K9+w62z4RpXFFTb31JYRaqWtMu4I3kJvk3gYV9W1uYu1yh7AVJf1+ibAtHB1OPXsNhasdTUrYE0pOCfp2Q7H8tfKs5FVpcX/jcifH79nsrJvItS6KBDhC5fsmUqbIkRtQlTge8XU8t57iVSowimlnYPujDn69AS90Eiofe7v8zOiapxsQyGv0sTm/CltG8fcZKQOtFy6WtHiIb6trhZ7VOjkXOsdBSJXGM3uBQVTtAHN7U4U5yoeNuSI4WInb8qZX9RHhspE9IPFpMgLEhi+Ooa7egsnYweAGngIdBzfY0Hmdk0CgYEA3Yx9Ynpuj32qSirVsO6GpErjMaTMr02empc+Tpem4Ww8WwlYDbyQd3g+sYj49bz6KMcSUVT8ueUa8RunZ4kaYJnXuAsMGDIae4HlkLnAHE8MeYyAgyOK9/0BecIEh7yOmza/5+pnj5J2cO2wi9gmcZ7JglsFaM9lG8/pXLmQs/MCgYEAwn8iLnCNS7fIoGXZEsXT3+5runYiu22FM61bpVvpXPDcy2UpBYNTS/GtNrfc4PL3gEXMHdlWVYt7pBP9tt+YydHd3W4av074OhabmU2C9Z2Irz1quGWdRobaurSs4wGFQz2wRQM1c8WEEHLDtpnF1YaffWKyAkldCMf0v3kdEzcCgYAniriiR/nNsH8hR63mK1xqnFcaeDB0JWVmcBIV0uldGIVZNjy6Duq1+AcwhqLwsS7w8j7pt0J4J1T8rZPeXdEQZsTiu04IT4D8hD2l7BFGvDEczJ/vR/u4sTZ/Ncq7M0M8nrP3v5WGQXzUQQgenZCW/OE4W+iGQWXpZd1fyFr0UwKBgQDA0R7G4UgCl6lq8m1YQg0BRDFCgll+bF4QVD212j+opxSNA7W+RGowsOAijfaIf/bEEO4BbXQ8jHI/wJ3XTSYAdnE4Uy5rGFPX6o/JVGGPM+TMrdt+AScBQzHWxMVcqyY8+gwk7sBwDJEXaV8DSMErJTI6MLz+rVJxISJPLDeZ0wKBgQCF2Ag0bkmHjOD/ZmUL0l2uD713tFK4D1vtyMODC9moxPTFAMKmVQyJDCnoDEBxPDDh722fIJzm/t1cluVbTkTB7GDspYHLbxG/4KEpVC4DQ3zu8R/sP1UXX98lChq5F/YrV/mE7q3rCVGJJ1Id6lZYXEUWo4TAqVchEwr5PAW2UQ==",
		//异步通知地址1123
		'notify_url' => "http://api.baizegame.com/index.php/Alipay/notify",

		//同步跳转
		'return_url' => "",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqFJ9S63v4E5z6ANLJeVa15IHK7csMsP0q43o9nWZXqsHD4WtcQ747WQVbksFr2zL1d2oB6gmye3FukuJS8E6/+OJEIHx+1D8y4RviT4hXjadxj1p8vOEYLv/UVrJ6g06FoG99IfzFW7Fnyd7rP5vRtPXVJ0izT5fw1/mAe2JCHfxDs4NDxaIEtyEpuYLdGq8/qgq+yEAyKdDuvcSbOi6yiaebXu0FFR/TDpEU30ZW9PLVq1ebqs4E/zvegAQFfwu+CSImClMrsYAI3pZ5Ak2weyiN0a06G2kHNEQPxML9mitPLwFaRePUkg+jnVTb7KOgi70lnu2aBKDkk1w2PiyNQIDAQAB",


);
