/* Presentation layer: falling Song Hỷ, curved photo caption and chapter reveals. */
(() => {
'use strict';
const boot=JSON.parse(document.getElementById('boot').textContent);if(boot.mode!=='invitation')return;
const $=(s,r=document)=>r.querySelector(s),$$=(s,r=document)=>[...r.querySelectorAll(s)];
let dispose=()=>{};
function mount({config:c,opened,boot:context=boot}){
 dispose();const controller=new AbortController(),observers=[],signal=controller.signal;
 const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
 // Keep paired names at the same size, and fit the longer name to its column.
 const canvas=document.createElement('canvas'),measure=canvas.getContext('2d');
 function fitGroup(selector,minSize){
  const names=$$(selector);if(!measure||!names.length||!names[0].getBoundingClientRect().width)return;
  names.forEach(el=>el.style.removeProperty('font-size'));
  let size=Infinity;
  names.forEach(el=>{const style=getComputedStyle(el),preferred=parseFloat(style.fontSize),width=el.getBoundingClientRect().width;measure.font=`${style.fontStyle} ${style.fontWeight} ${preferred}px ${style.fontFamily}`;const text=style.textTransform==='uppercase'?el.textContent.toLocaleUpperCase('vi'):el.textContent;const needed=measure.measureText(text).width;size=Math.min(size,preferred,preferred*(width-3)/Math.max(needed,1));});
  names.forEach(el=>el.style.fontSize=Math.max(minSize,Math.floor(size*10)/10)+'px');
 }
 function fitNames(){if(signal.aborted)return;fitGroup('.hero-names h1',12);fitGroup('.couple-names h2',22);}
 requestAnimationFrame(fitNames);
 if(document.fonts?.ready)document.fonts.ready.then(fitNames);
 if('ResizeObserver' in window){let previousWidth=0;const resize=new ResizeObserver(entries=>{const width=entries[0].contentRect.width;if(Math.abs(width-previousWidth)>.5){previousWidth=width;requestAnimationFrame(fitNames);}});resize.observe($('.wedding-card'));observers.push(resize);}
 const envelope=$('.envelope');
 if(envelope&&!reduced){
  const rain=document.createElement('div');rain.className='hy-particles';rain.setAttribute('aria-hidden','true');
  for(let i=0;i<18;i++){const symbol=document.createElement('span');symbol.textContent='囍';symbol.style.cssText=`--x:${(i*37+9)%100}%;--size:${10+(i*7)%14}px;--duration:${17+(i*3)%13}s;--delay:-${(i*11)%29}s;--drift:${i%2?'-':''}${12+(i*13)%42}px`;rain.append(symbol);}
  envelope.prepend(rain);
 }
 if(c.theme.template==='heritage'){
  const caption=$('.hero-portrait>p');
  if(caption&&caption.textContent.trim()){
   const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');svg.setAttribute('viewBox','0 0 300 60');svg.setAttribute('aria-label',caption.textContent);svg.setAttribute('role','img');
   svg.innerHTML='<defs><path id="invitation-caption-arc" d="M15 54 Q150 -12 285 54"/></defs><text fill="currentColor" font-family="Playfair Display, Georgia, serif" font-size="13" letter-spacing="2.7"><textPath href="#invitation-caption-arc" startOffset="50%" text-anchor="middle"></textPath></text>';
   $('textPath',svg).textContent=caption.textContent;caption.replaceChildren(svg);
  }
  const reception=$('.reception-section'),rsvp=$('.rsvp-section'),venue=$('.venue-card');
  if(reception&&rsvp)reception.after(rsvp);
  if(venue){const section=document.createElement('section');section.className='wedding-section venue-section';const heading=document.createElement('h2');heading.textContent=boot.language==='jp'?'会場のご案内':c.reception.type+' sẽ tổ chức tại';section.append(heading,venue);(rsvp||reception).after(section);}
  const countdown=$('#countdown'),calendarSlot=$('#wedding-calendar-slot');if(countdown&&calendarSlot)calendarSlot.before(countdown);
 }
 // Native radios retain keyboard and screen-reader support; the original select
 // remains the canonical value consumed by the existing RSVP storage contract.
 const form=$('[data-public-form="rsvp"]'),select=form&&$('[name="attending"]',form);
 if(select){
  const label=select.closest('label'),wrapper=document.createElement('div');wrapper.className='attendance-wrapper';
  const options=document.createElement('fieldset');options.className='attendance-options';options.innerHTML='<legend>Bạn sẽ đến chứ?</legend><label class="attendance-option"><input name="attendanceChoice" type="radio" value="yes" required><span class="attendance-icon" aria-hidden="true">✓</span><span>Tôi sẽ đến chung vui</span></label><label class="attendance-option"><input name="attendanceChoice" type="radio" value="no" required><span class="attendance-icon" aria-hidden="true">×</span><span>Rất tiếc, tôi không thể đến</span></label>';
  label.replaceWith(wrapper);select.hidden=true;select.required=false;select.tabIndex=-1;select.removeAttribute('aria-label');wrapper.append(select,options);
  options.addEventListener('change',e=>{if(e.target.name!=='attendanceChoice')return;select.value=e.target.value;select.dispatchEvent(new Event('change',{bubbles:true}));},{signal});
  form.addEventListener('change',()=>{$$('input',options).forEach(el=>el.checked=el.value===select.value);},{signal});
 }
 if(opened&&!context.preview&&!reduced&&'IntersectionObserver' in window){
  const observer=new IntersectionObserver(entries=>{entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-visible');observer.unobserve(entry.target);}});},{threshold:.06,rootMargin:'0px 0px -18px 0px'});
  $$('.wedding-section').forEach(section=>{section.classList.add('section-enter');observer.observe(section);});observers.push(observer);
 }
 window.WeddingLanguage?.translate(document.body);
 dispose=()=>{controller.abort();observers.forEach(o=>o.disconnect());};
}
document.addEventListener('wedding:render',e=>mount(e.detail));
mount({config:boot.config,opened:boot.preview,boot});
})();
