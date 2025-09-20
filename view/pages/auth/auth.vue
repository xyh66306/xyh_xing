<template>
	<view class="page">
		<view class="form">
			<u--form labelPosition="top" :model="loginForm" :rules="rules" ref="loginForm" labelWidth="auto">
				<u-form-item label="身份证人像面" prop="sfz_f" borderBottom ref="sfz_f">
					<template v-if="sfz_bimage">
						<view class="sfzbg">
							<image :src="sfz_bimage" mode=""></image>
						</view>
					</template>
					<template v-else>
						<u-upload :fileList="sfz_f" @afterRead="afterRead" @delete="deletePic" :maxCount="1" name="sfz_f"
							width="630rpx" height="150px" uploadText="点击上传身份证人像面"></u-upload>	
					</template>
			
				</u-form-item>
				<u-form-item label="身份证国徽面" prop="sfz_b" borderBottom ref="sfz_b">					
					<template v-if="sfz_fimage">
						<view class="sfzbg">
							<image :src="sfz_fimage" mode=""></image>
						</view>
					</template>
					<template v-else>
					<u-upload :fileList="sfz_b" @afterRead="afterRead" @delete="deletePic" :maxCount="1" name="sfz_b"
						width="630rpx" height="150" uploadText="点击上传身份证国徽面"></u-upload>
					</template>	
				</u-form-item>
				<u-form-item label="手持身份证" prop="sfz_p" borderBottom ref="sfz_p">
					<template v-if="sfz_pimage">
						<view class="sfzbg">
							<image :src="sfz_pimage" mode=""></image>
						</view>
					</template>
					<template v-else>
					<u-upload :fileList="sfz_p" @afterRead="afterRead" @delete="deletePic" :maxCount="1" name="sfz_p"
						width="630rpx" height="150" uploadText="点击上传手持身份证"></u-upload>
					</template>	
				</u-form-item> 
			</u--form>
		</view>	
		<u-button type="primary" @click="authbtn" shape="circle" :disabled="isauth">确认提交</u-button>
	</view>			
</template>

<script>
	export default {
		data() {
			return {
				loginForm: {
					sfz_f: '',
					sfz_b: '',
					sfz_p: ''
				},
				rules: {
					sfz_f: [{
						required: true,
						message: '请上传身份证人像面',
						trigger: ['change']
					}],
					sfz_b: [{
						required: true,
						message: '请上传身份证国徽面',
						trigger: ['change']
					}],
					sfz_p: [{
						required: true,
						message: '请上传手持身份证',
						trigger: ['change']
					}],
				},
				tips: '',
				sfz_f: [],
				sfz_b: [],
				sfz_p: [],
				sfz_bimage:'',
				sfz_fimage:'',
				sfz_pimage:'',
				isauth:false,
			}
		},
		onLoad(e) {
			this.getszfinfo();
		},
		methods: {
			getszfinfo(){
				uni.$u.http.post('/api/user/getsfzinfo').then(res => {
					if(res.code==1){
	
						this.sfz_bimage = res.data.sfz_bimage;
						this.sfz_fimage = res.data.sfz_fimage;
						this.sfz_pimage = res.data.sfz_pimage;
						if(res.data.sfz_bimage && res.data.sfz_fimage && res.data.sfz_pimage){
							this.isauth = true;
						}
					}				
				}).catch(res => {
					uni.$u.toast(res.msg);
					console.log(res);
				});
			},
			authbtn() {
				this.$refs.loginForm.validate().then(res => {
					uni.$u.http.post('/api/user/updatesfz', this.loginForm).then(res => {
						if(res.code==1){
							uni.$u.toast('已上传，审核中');
							uni.navigateBack();	
						}else{
							uni.$u.toast(res.msg);
						}					
					}).catch(res => {
						uni.$u.toast(res.msg);
						console.log(res);
					});
				}).catch(res => {})
			},
			codeChange(text) {
				this.tips = text;
			},
			// 新增图片
			async afterRead(event) {
				let lists = [].concat(event.file);
				let item = {
					...event.file,
					status: 'uploading',
					message: '上传中'
				};
				this[event.name] = [].concat(item);
				const res = await uni.$u.http.upload('/api/common/upload', {
					filePath: event.file.url,
					name: 'file',
				}).then(res => {
					if (res?.code == 1) {
						this[event.name].splice(0, 1, Object.assign(item, {
							status: 'success',
							message: '',
							url: res.data.fullurl
						}));
						this.loginForm[event.name] = res.data.url;
					} else {
						uni.$u.toast(res.msg || '上传失败');
						this[event.name] = [];
						this.loginForm[event.name] = '';
					}
					this.$refs.loginForm.validateField(event.name);
				}).catch(res => {
					this[event.name] = [];
					this.loginForm[event.name] = '';
				});
			},
			deletePic(event) {
				this[event.name].splice(event.index, 1);
				this.loginForm[event.name] = '';
			},
		}
	}
</script>


<style lang="scss" scoped>
	.page {
		width: 100vw;
		min-height: 100vh;
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: calc(var(--status-bar-height) + 50rpx) 60rpx 100rpx;
	}

	.welcome {
		font-size: 24px;
		font-weight: bold;
		margin-bottom: 60rpx;
	}

	.form {
		margin-bottom: 80rpx;
	}

	.sfzbg {
		width:620rpx;
		height:400rpx;
		image {
			width: 100%;
			height: 100%;
		}
	}

	::v-deep .u-upload__button {
		background-color: #fff;
	}
</style>