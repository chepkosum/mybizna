"use strict";(self["webpackChunk"]=self["webpackChunk"]||[]).push([[963],{7963:function(e,t,l){l.r(t),l.d(t,{default:function(){return p}});var a=l(9199);const o={class:"grid grid-cols-12 gap-2"},n={class:"text-xs italic font-semibold border-b border-dotted border-gray-100 text-blue-900 my-2"};function r(e,t,l,r,d,m){const c=(0,a.resolveComponent)("FormKit"),p=(0,a.resolveComponent)("edit-render");return e.layout_fetched?((0,a.openBlock)(),(0,a.createBlock)(p,{key:0,path_param:e.tmp_path_param,model:e.model},{default:(0,a.withCtx)((()=>[(0,a.createElementVNode)("div",o,[((0,a.openBlock)(!0),(0,a.createElementBlock)(a.Fragment,null,(0,a.renderList)(e.layout,((t,l)=>((0,a.openBlock)(),(0,a.createElementBlock)("div",{key:l,class:(0,a.normalizeClass)(t.class)},[(0,a.createElementVNode)("h4",n,(0,a.toDisplayString)(t.label),1),((0,a.openBlock)(!0),(0,a.createElementBlock)(a.Fragment,null,(0,a.renderList)(t.fields,((t,l)=>((0,a.openBlock)(),(0,a.createElementBlock)(a.Fragment,{key:l},["recordpicker"==t.html?((0,a.openBlock)(),(0,a.createBlock)(c,{key:0,label:t.label,button_label:t.button_label,id:t.name,type:"recordpicker",setting:t.picker,modelValue:e.model[t.name],"onUpdate:modelValue":l=>e.model[t.name]=l,"inner-class":"$reset formkit-inner","wrapper-class":"$reset formkit-wrapper"},null,8,["label","button_label","id","setting","modelValue","onUpdate:modelValue"])):"select"==t.html||"radio"==t.html||"checkbox"==t.html?((0,a.openBlock)(),(0,a.createBlock)(c,{key:1,modelValue:e.model[t.name],"onUpdate:modelValue":l=>e.model[t.name]=l,options:t.options,label:t.label,id:t.name,type:t.html},null,8,["modelValue","onUpdate:modelValue","options","label","id","type"])):((0,a.openBlock)(),(0,a.createBlock)(c,{key:2,modelValue:e.model[t.name],"onUpdate:modelValue":l=>e.model[t.name]=l,label:t.label,id:t.name,type:t.html},null,8,["modelValue","onUpdate:modelValue","label","id","type"]))],64)))),128))],2)))),128))])])),_:1},8,["path_param","model"])):(0,a.createCommentVNode)("",!0)}var d={props:{path_param:{type:Array,default:()=>[]}},created(){this.tmp_path_param=this.path_param.length?this.path_param:this.$route.meta.path;var e=this.$route.meta.path;window.axios.get("fetch_layout/"+e[0]+"/"+e[1]+"/edit").then((e=>{this.layout=e.data.layout,e.data.fields.forEach((e=>{this.model[e]=""})),this.layout_fetched=!0})).catch((e=>{console.log(e)}))},data:function(){return{tmp_path_param:[],layout_fetched:!1,id:null,model:{},layout:{}}}},m=l(89);const c=(0,m.Z)(d,[["render",r]]);var p=c}}]);
//# sourceMappingURL=963.js.map