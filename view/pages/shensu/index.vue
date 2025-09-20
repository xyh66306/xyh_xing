<template>
	<view class="page">
		<view class="payinfo">
			<view class="u-info">备注</view>
			<view class="flex u-border-bottom">
				<u-input type="textarea" border="true" placeholder="请填写订单申诉原因" v-model="intro" />
			</view>
			<view class="u-info">凭证</view>
			<view class="flex u-border-bottom">
				<u-upload :fileList="ewmlst" @afterRead="afterRead" @delete="deletePic" :maxCount="1" name="ewmlst"
					width="150" height="150" :previewFullImage="true" uploadText="点击上传凭证"></u-upload>
			</view>			
		</view>
		<view class="payinfo sysStatus" v-if="ishistory">
			<view class="u-info">申诉状态</view>
			<view class="flex u-border-bottom">
				{{statusArr[status]}}
			</view>
			<view class="u-info" v-if="sysbeizhu">系统回复</view>
			<view class="flex u-border-bottom" v-if="sysbeizhu">
				{{sysbeizhu || ""}}
			</view>			
		</view>
		<u-button type="primary" @click="submit()" v-if="status!=1">点击申诉</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				id:'',
				orderid:'',
				intro:'',
				border: false,
				ewmlst:[],
				pz_image:'',
				ishistory:false,
				sysbeizhu:'',
				statusArr:["申诉中",'已处理','已驳回'],
				status:0,
			}
		},
		onLoad(e) {
			if(e.id){
				this.orderid = e.id
				this.getHistory();
			}
		},
		methods: {
			submit(){				
				if(!this.intro){
					return uni.$u.toast("申诉原因不能为空");	
				}
				if(!this.orderid){
					return uni.$u.toast("订单号不能为空");	
				}
				if(this.status==1){
					return uni.$u.toast("申诉已处理，请勿重复提交");	
				}
				uni.$u.http.post('/api/cash/shensu', {
					id:this.id,
					orderid: this.orderid,
					beizhu:this.intro,
					pz_image:this.pz_image
				}).then(res => {
					uni.$u.toast(res.msg);				
				}).catch(res => {
					console.log(res)
				});
			},
			getHistory(){
				if(!this.orderid){
					return uni.$u.toast("订单号不能为空");	
				}
				uni.$u.http.post('/api/cash/getshensu', {
					orderid: this.orderid,
				}).then(res => {
					if(res.code==1){
						this.ishistory = true
						this.intro = res.data.beizhu
						this.pz_image = res.data.pz_image
						this.sysbeizhu = res.data.sysbeizhu
						this.status = res.data.status
						this.ewmlst =[res.data.pz_image];
					}
				}).catch(res => {
					console.log(res)
				});
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
						this.pz_image = res.data.url;
					} else {
						uni.$u.toast(res.msg || '上传失败');
						this[event.name] = [];
						this.pz_image = '';
					}
				}).catch(res => {
					this[event.name] = [];
					this.pz_image = '';
				});
			},
			deletePic(event) {
				this[event.name].splice(event.index, 1);
				this.pz_image = '';
			},
		}
	}
</script>

<style lang="scss">
	.page {
		padding: 30rpx;
	}

	.payinfo {
		padding: 30rpx;
		background-color: #fff;
		border-radius: 10rpx;
		margin-bottom: 30rpx;

		.flex {
			display: flex;
			align-items: center;
			padding: 20rpx 0;
			margin-bottom: 30rpx;

			.copy {
				margin-left: auto;
			}
		}

		.tips {
			font-size: 14px;
			color: $u-error;
		}
	}
</style>