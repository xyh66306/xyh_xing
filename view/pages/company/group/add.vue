<template>
	<view>
		<view class="item">
			<view class="left">
				团队名称：
			</view>
			<view class="right">
				<input type="text" v-model="teamName" placeholder="请输入团队名称" class="input" />
			</view>
		</view>
		<view class="addbtn">
			<u-button type="primary" @click="addTeam" shape="circle">立即创建</u-button>
		</view>
	</view>
</template>
<script>
	export default {
		data() {
			return {
				id:'',
				teamName: ''
			}
		},
		onLoad(e) {
			if(e.id){
				uni.setNavigationBarTitle({
					title:"团队编辑"
				})
				this.id = e.id;
				this.getInfo();
			}
		},
		methods: {
			getInfo(){
				uni.$u.http.post('/api/team/getTeamInfo', {
					id:this.id,
				}).then(res => {
					if(res.code==1){
						this.teamName = res.data.name;
					}else{
						uni.$u.toast(res.msg);	
					}
				}).catch(res => {
					console.log(res)
				});
			},
			addTeam(){
				if(!this.teamName){
					return uni.$u.toast("团队名称不能为空");	
				}
				uni.$u.http.post('/api/team/add', {
					name:this.teamName,
				}).then(res => {
					uni.$u.toast(res.msg);	
					uni.navigateBack()
				}).catch(res => {
					console.log(res)
				});
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #f5f5f5;
	}
	.item {
		display: flex;
		padding:0 20rpx;
		border-bottom:#f3f3f3 2rpx solid;
		background-color: #fff;
		margin-top:5rpx;
		.left {
			width:160rpx;
			height:90rpx;
			line-height: 90rpx;
			color:#333;
		}
		.right {
			width:450rpx;
			height:90rpx;
			.input {
				width:100%;
				height:100%;
				
			}
		}		
	}
	.addbtn {
		width:710rpx;
		margin:50rpx auto 0;
	}
</style>