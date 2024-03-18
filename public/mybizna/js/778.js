"use strict";(self["webpackChunk"]=self["webpackChunk"]||[]).push([[778],{1778:function(e,t,o){o.r(t),o.d(t,{default:function(){return C}});var a=o(7001);const s=e=>((0,a.pushScopeId)("data-v-49914220"),e=e(),(0,a.popScopeId)(),e),l={class:"col-md-6"},r={class:"authincation-content border rounded shadow bg-white"},n={class:"m-3"},c={class:"auth-form"},d={class:"text-center mb-3"},m=["src"],i=s((()=>(0,a.createElementVNode)("h4",{class:"text-center my-4"},"Login to your account",-1))),u={class:"form-group mt-2 w-96 max-w-full",style:{margin:"0 auto"}},h=s((()=>(0,a.createElementVNode)("label",{class:"mb-1"},[(0,a.createElementVNode)("strong",null,"Email")],-1))),p={class:"form-group mt-2 w-96 max-w-full",style:{margin:"0 auto"}},g=s((()=>(0,a.createElementVNode)("label",{class:"mb-1"},[(0,a.createElementVNode)("strong",null,"Password")],-1))),w={class:"form-row d-flex justify-content-between mt-4 mb-2 w-96 max-w-full",style:{margin:"0 auto"}},N=s((()=>(0,a.createElementVNode)("div",{class:"form-group"},[(0,a.createElementVNode)("div",{class:"custom-control custom-checkbox ml-1"},[(0,a.createElementVNode)("input",{type:"checkbox",class:"custom-control-input",id:"basic_checkbox_1"}),(0,a.createElementVNode)("label",{class:"custom-control-label",for:"basic_checkbox_1"},"Remember my preference")])],-1))),V={class:"form-group"},b={class:"text-center mt-2"},E=["loading"],v={key:0,class:"new-account mt-5"},f=s((()=>(0,a.createElementVNode)("br",null,null,-1)));function x(e,t,o,s,x,k){const y=(0,a.resolveComponent)("router-link"),_=(0,a.resolveComponent)("b-button");return(0,a.openBlock)(),(0,a.createElementBlock)("div",{class:(0,a.normalizeClass)([e.$is_backend?"h-screen h-100":"","mb-2 row justify-content-center align-items-center"])},[(0,a.createElementVNode)("div",l,[(0,a.createElementVNode)("div",r,[(0,a.createElementVNode)("div",n,[(0,a.createElementVNode)("div",c,[(0,a.createElementVNode)("div",d,[(0,a.createElementVNode)("img",{src:this.$assets_url+"images/logos/logo.png",alt:"",style:{margin:"0 auto","max-width":"120px"}},null,8,m)]),i,(0,a.createElementVNode)("div",null,[(0,a.createElementVNode)("div",u,[h,(0,a.withDirectives)((0,a.createElementVNode)("input",{type:"text",class:"form-control","onUpdate:modelValue":t[0]||(t[0]=t=>e.model.username=t)},null,512),[[a.vModelText,e.model.username]])]),(0,a.createElementVNode)("div",p,[g,(0,a.withDirectives)((0,a.createElementVNode)("input",{type:"password",class:"form-control","onUpdate:modelValue":t[1]||(t[1]=t=>e.model.password=t)},null,512),[[a.vModelText,e.model.password]])]),(0,a.createElementVNode)("div",w,[N,(0,a.createElementVNode)("div",V,[(0,a.createVNode)(y,{to:"/forgotpassword"},{default:(0,a.withCtx)((()=>[(0,a.createTextVNode)("Forgot Password?")])),_:1})])]),(0,a.createElementVNode)("div",b,[(0,a.createElementVNode)("button",{type:"submit",class:"btn text-white bg-blue-600",onClick:t[2]||(t[2]=(...e)=>k.login&&k.login(...e)),loading:e.loading}," LOGIN ",8,E)])]),e.has_register?((0,a.openBlock)(),(0,a.createElementBlock)("div",v,[(0,a.createElementVNode)("p",null,[(0,a.createTextVNode)(" Don't have an account? "),f,(0,a.createVNode)(_,{variant:"success"},{default:(0,a.withCtx)((()=>[(0,a.createVNode)(y,{to:"/register",class:"text-white"},{default:(0,a.withCtx)((()=>[(0,a.createTextVNode)("CREATE ACCOUNT")])),_:1})])),_:1})])])):(0,a.createCommentVNode)("",!0)])])])])],2)}o(4114);var k={watch:{"$store.state.auth.token":{immediate:!0,handler(){this.$store.getters["auth/loggedIn"]&&(this.$store.dispatch("auth/getUser",{that:this}),window.is_frontend?this.$router.push("/dashboard"):this.$router.push("/manage/dashboard"))}}},created(){window.autologin&&this.$store.dispatch("auth/autologin",{that:this})},data:()=>({loading:!1,has_register:!1,model:{username:"",password:""}}),methods:{login(){let e={username:this.model.username,password:this.model.password,that:this};this.$store.dispatch("auth/authenticate",e)}}},y=o(1241);const _=(0,y.A)(k,[["render",x],["__scopeId","data-v-49914220"]]);var C=_}}]);
//# sourceMappingURL=778.js.map