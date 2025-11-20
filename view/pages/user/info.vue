<template>
	<view class="wrap">
		<u--form labelPosition="left" :model="model1" :rules="rules" ref="uForm">
			<u-form-item label="头像：" labelWidth="100" prop="userInfo.avatar" borderBottom ref="item1">
				<u--image :src="model1.userInfo.avatar" shape="circle" width="50px" height="50px" v-if="model1.userInfo.avatar && model1.userInfo.avatar !='/assets/img/avatar.png'"></u--image>
				<u-avatar :text="model1.userInfo.nickname.substr(-1)" randomBgColor color-index="18" size="50" v-else></u-avatar>
			</u-form-item>
			<u-form-item label="昵称：" labelWidth="100" prop="userInfo.nickname" borderBottom ref="item1">
				<u--input v-model="model1.userInfo.nickname" border="none"></u--input>
			</u-form-item>
			<u-form-item label="姓名：" labelWidth="100" prop="userInfo.username" borderBottom ref="item1">
				<u--input v-model="model1.userInfo.username" border="none"></u--input>
			</u-form-item>	
			<u-form-item label="邮箱：" labelWidth="100" prop="userInfo.email" borderBottom ref="item1">
				<u--input v-model="model1.userInfo.email" border="none"></u--input>
			</u-form-item>				
			<u-form-item label="手机号：" labelWidth="100" prop="userInfo.mobile" borderBottom ref="item1">
				<u--input v-model="model1.userInfo.mobile" border="none"></u--input>
			</u-form-item>					
		</u--form>
		<view class="footbtn">
			<u-button type="primary" @click="submit">提交</u-button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				showSex: false,
				model1:{
					userInfo: {
						avatar:'',
						nickname: '',
						username: '',
						mobile:'',
						email:''
					}
				},
				rules: {
					'userInfo.nickname': {
						type: 'string',
						required: true,
						message: '请填写昵称',
						trigger: ['blur', 'change']
					},
					'userInfo.username': {
						type: 'string',
						required: true,
						message: '请填写姓名',
						trigger: ['blur', 'change']
					},
					'userInfo.email': {
						type: 'email',
						required: true,
						message: '请填写邮箱',
						trigger: ['blur', 'change']
					},					
					'userInfo.mobile': [
						{
							type: 'string',
							required: true,
							message: '请填写手机号',
							trigger: ['blur', 'change']
						},
						{
							validator: (rule, value, callback) => {
								return uni.$u.test.mobile(value);
							},
							message: '手机号码不正确',
							trigger: ['change','blur'],
						}
					]
				},
				switchVal: false
			};
		},
		onLoad() {
			this.getUserInfo();
		},
		methods: {
			getUserInfo(){
				uni.$u.http.post('/api/user/getUserinfo').then(res => {
					if(res.code == 1) {
						let user =res.data;
						this.model1.userInfo.avatar = user.avatar
						this.model1.userInfo.nickname = user.nickname
						this.model1.userInfo.username = user.username
						this.model1.userInfo.mobile = user.mobile
						this.model1.userInfo.email = user.email
					}
				})
			},			
			submit() {
				let that = this
				that.$refs.uForm.validate().then(res => {
					uni.$u.http.post('/api/user/updateInfo',that.model1.userInfo).then(res => {
						if(res.code == 1) {
							that.getUserInfo();
							uni.$u.toast('已更新')
						} else {
							uni.$u.toast('更新失败')
						}
					})
				}).catch(errors => {
					uni.$u.toast('校验失败')
				})
				
			}			
		},
		onReady() {
			//如果需要兼容微信小程序，并且校验规则中含有方法等，只能通过setRules方法设置规则。
			this.$refs.uForm.setRules(this.rules)
		},
	};
</script>

<style lang="scss" scoped>
	page {
		background: #fff;
	}
	.wrap {
		padding:0 20rpx;
	}
	.footbtn {
		position: fixed;
		bottom: 0rpx;
		left:0rpx;
		width:100%;
	}
</style>