'use strict';
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../i18n.js'),'utf8');
function runtime({native,legacy=true,language='vn'}={}){
 const calls={removed:false,restored:false,selected:false,legacy:0};
 const field={value:'',style:{},setAttribute(){},focus(){},select(){calls.selected=true;},setSelectionRange(a,b){assert.equal(a,0);assert.equal(b,field.value.length);},remove(){calls.removed=true;}};
 const document={getElementById:()=>({textContent:JSON.stringify({language})}),querySelectorAll:()=>[],activeElement:{focus(){calls.restored=true;}},body:{append(){}},createElement:()=>field,execCommand(command){calls.legacy++;assert.equal(command,'copy');assert.ok(calls.selected);return legacy;}};
 const context={window:{},navigator:{clipboard:native?{writeText:native}:undefined},document};
 vm.runInNewContext(source,context);
 return {api:context.window.WeddingLanguage,calls,field};
}
(async()=>{
 let received='';let run=runtime({native:async text=>{received=text;}});
 assert.equal(await run.api.copyText('00123456789'),true);assert.equal(received,'00123456789');assert.equal(run.calls.legacy,0);
 run=runtime();assert.equal(await run.api.copyText('00123456789'),true);assert.equal(run.field.value,'00123456789');assert.ok(run.calls.removed&&run.calls.restored);
 run=runtime({native:async()=>{throw Error('Clipboard denied');}});assert.equal(await run.api.copyText('00123456789'),true);assert.equal(run.calls.legacy,1);
 run=runtime({legacy:false,language:'jp'});assert.equal(await run.api.copyText('00123456789'),false);assert.equal(run.api.t('Sao chép số tài khoản'),'口座番号をコピー');assert.equal(run.api.t('Guest Name'),'Guest Name');
 assert.equal(await run.api.copyText(''),false);
 console.log('PASS: native clipboard, HTTP fallback, denied clipboard fallback, manual-copy signal, leading zeros and Japanese labels.');
})().catch(error=>{console.error(error);process.exitCode=1;});
