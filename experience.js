/* Public invitation enhancements. Every mount cleans timers/listeners from live preview. */
(() => {
'use strict';
const initial=JSON.parse(document.getElementById('boot').textContent);if(initial.mode!=='invitation')return;
const $=(s,r=document)=>r.querySelector(s),$$=(s,r=document)=>[...r.querySelectorAll(s)];
const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let cleanup=()=>{},wishes=initial.wishes||[];
document.addEventListener('wedding:render',e=>mount(e.detail));
function calendar(date){
 if(!/^\d{4}-\d{2}-\d{2}$/.test(date))return '';
 const [year,month,day]=date.split('-').map(Number),first=new Date(Date.UTC(year,month-1,1)),offset=(first.getUTCDay()+6)%7,last=new Date(Date.UTC(year,month,0)).getUTCDate();
 const cells=Array.from({length:Math.ceil((offset+last)/7)*7},(_,i)=>{const n=i-offset+1;return n<1||n>last?'<td></td>':`<td ${n===day?'class="wedding-day"':''}><time datetime="${year}-${String(month).padStart(2,'0')}-${String(n).padStart(2,'0')}" ${n===day?'aria-label="Ngày cưới"':''}>${n}</time></td>`;});
 const weekday=new Date(date+'T12:00:00+07:00').toLocaleDateString('vi-VN',{weekday:'long',timeZone:'Asia/Ho_Chi_Minh'});
 return `<div class="wedding-calendar"><span class="calendar-clip" aria-hidden="true"><svg viewBox="0 0 24 64" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M8 44V11a7 7 0 0 1 14 0v38a10 10 0 0 1-20 0V16a4 4 0 0 1 8 0v31a2 2 0 0 1-4 0V19"/></svg></span><div class="calendar-title"><span>${esc(weekday)}</span><strong>${String(day).padStart(2,'0')}.${String(month).padStart(2,'0')}.${year}</strong><small>NGÀY CHÚNG MÌNH CHUNG ĐÔI</small></div><table aria-label="Lịch ngày cưới"><caption>Tháng ${month} · ${year}</caption><thead><tr>${['T2','T3','T4','T5','T6','T7','CN'].map(d=>'<th scope="col">'+d+'</th>').join('')}</tr></thead><tbody>${Array.from({length:cells.length/7},(_,i)=>'<tr>'+cells.slice(i*7,i*7+7).join('')+'</tr>').join('')}</tbody></table><p>♡ Một ngày để nhớ, một đời để thương.</p></div>`;
}
function mount({config:c,opened,boot=initial}){
 cleanup();const controller=new AbortController(),signal=controller.signal,timers=[],observers=[];let raf=0,auto=false,lastFrame=0,position=window.scrollY;
 const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
 document.body.dataset.template=c.theme.template;
 if(c.theme.template==='editorial'&&$('.envelope')&&/^(?:(?:assets|uploads)\/[\w.-]+|https:\/\/)/i.test(c.hero.image))$('.envelope').style.backgroundImage='linear-gradient(#20161d77,#20161dcc),url('+JSON.stringify(c.hero.image)+')';
 document.documentElement.style.setProperty('--text-scale',String(c.theme.textScale));document.documentElement.style.setProperty('--name-scale',String(c.theme.nameScale));
 const card=$('.wedding-card');if(!card)return;
 if(c.theme.template==='editorial'&&!$('.hero-portrait img'))$('.wedding-hero').style.background=c.theme.color;
 if(boot.guest){const greeting=document.createElement('div');greeting.className='personal-invitation';greeting.innerHTML=`<span class="wedding-kicker">TRÂN TRỌNG KÍNH MỜI</span><h2>${esc(boot.guest.salutation)} <strong>${esc(boot.guest.name)}</strong></h2><p>Đến chung vui trong ngày hạnh phúc của chúng mình.</p>`;$('.family-section').prepend(greeting);}
 if(c.experience.calendar&&c.reception.enabled&&c.reception.date){$('#wedding-calendar-slot')?.insertAdjacentHTML('beforeend',calendar(c.reception.date));}
 $$('.wedding-section').forEach((s,i)=>{s.dataset.sectionNumber=String(i+1).padStart(2,'0');});
 const rsvp=$('[data-public-form="rsvp"]');
 if(rsvp){
  const transport=document.createElement('div');transport.className='transport-form';transport.innerHTML='<label class="transport-choice"><input type="checkbox" name="transport"> <span>Tôi cần xe đưa đón</span><small>Để gia đình sắp xếp chuyến đi chu đáo hơn.</small></label><fieldset class="pickup-fields" hidden disabled><legend>Thông tin đón khách</legend><label>Địa chỉ đón<textarea name="pickupAddress" rows="2" maxlength="1000" placeholder="Số nhà, đường, xã/phường, tỉnh/thành" required></textarea></label><div class="pickup-grid"><label>Số người đón<input name="pickupCount" type="number" min="1" max="20" value="1" required></label><label>Điện thoại liên lạc<input name="phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="25" pattern="[+0-9() .-]{8,25}" placeholder="09xx xxx xxx" required></label></div><p>Thông tin chỉ gửi đến người tổ chức để sắp xếp xe và liên hệ đón khách.</p></fieldset>';
  $('.honeypot',rsvp).before(transport);
  const update=()=>{const attending=$('[name="attending"]',rsvp).value==='yes',choice=$('[name="transport"]',rsvp),fields=$('.pickup-fields',rsvp),count=Number($('[name="count"]',rsvp).value||1);transport.hidden=!attending;if(!attending)choice.checked=false;fields.hidden=!(attending&&choice.checked);fields.disabled=fields.hidden;$('[name="pickupCount"]',rsvp).max=String(count);};
  rsvp.addEventListener('change',update,{signal});rsvp.addEventListener('input',e=>{if(e.target.name==='count')update();},{signal});update();
 }
 let galleryTimer=null,photo=0;
 const gallery=$('.wedding-gallery');
 if(gallery&&c.gallery.layout==='3d'){
  gallery.classList.add('album-three-d');gallery.setAttribute('aria-label','Album ảnh cưới 3D');gallery.setAttribute('tabindex','0');
  const photos=$$('button',gallery),n=photos.length,period=Number(c.experience.albumInterval)*1000;
  gallery.style.setProperty('--album-transition',c.experience.albumTransition+'s');
  let wantsAuto=c.experience.albumAuto&&!reduced,inView=false,hovered=false,changing=false,progress=null;
  gallery.insertAdjacentHTML('afterend','<div class="album-controls"><button type="button" class="album-prev" aria-label="Ảnh trước">‹</button><span class="album-counter" aria-live="polite"></span><button type="button" class="album-next" aria-label="Ảnh tiếp theo">›</button><button type="button" class="album-play" aria-label="Bật tự chuyển ảnh" aria-pressed="false">▶</button></div><span class="album-progress-track" aria-hidden="true"><span class="album-progress"></span></span>');
  const controls=gallery.nextElementSibling,play=$('.album-play',controls),counter=$('.album-counter',controls),bar=$('.album-progress',controls.nextElementSibling);
  const imageAt=i=>$('img',photos[(i+n)%n]);
  function warm(i){const img=imageAt(i);img.loading='eager';img.decoding='async';return img.decode?img.decode().catch(()=>{}):Promise.resolve();}
  function arrange(){photos.forEach((el,i)=>{let d=i-photo;if(d>n/2)d-=n;if(d< -n/2)d+=n;el.style.setProperty('--offset',String(d));el.style.setProperty('--abs-offset',String(Math.abs(d)));el.dataset.active=String(d===0);el.style.zIndex=String(20-Math.abs(d));el.style.willChange=Math.abs(d)<=2?'transform':'auto';el.style.opacity=Math.abs(d)>2?'0':String(Math.max(.18,1-Math.abs(d)*.36));el.style.visibility=Math.abs(d)>2?'hidden':'visible';el.tabIndex=d===0?0:-1;});counter.textContent=`${photo+1} / ${n}`;warm(photo);warm(photo+1);warm(photo-1);}
  function pause(){clearTimeout(galleryTimer);galleryTimer=null;progress?.cancel();progress=null;}
  function updateButton(){play.textContent=wantsAuto?'Ⅱ':'▶';play.setAttribute('aria-label',wantsAuto?'Dừng tự chuyển ảnh':'Bật tự chuyển ảnh');play.setAttribute('aria-pressed',String(wantsAuto));play.disabled=reduced||!!boot.preview||n<2;}
  function schedule(delay=period){
   pause();updateButton();
   if(!wantsAuto||!opened||boot.preview||reduced||n<2||!inView||hovered||document.hidden)return;
   if(bar.animate)progress=bar.animate([{transform:'scaleX(0)'},{transform:'scaleX(1)'}],{duration:delay,fill:'forwards'});
   galleryTimer=setTimeout(async()=>{if($('#photo-dialog')?.open||gallery.contains(document.activeElement)){schedule();return;}await advance(1,false);},delay);
  }
  async function advance(d,manual=true){if(changing)return;changing=true;pause();await warm(photo+d);if(!signal.aborted){photo=(photo+d+n)%n;arrange();schedule(manual?7000:period);}changing=false;}
  $('.album-prev',controls).onclick=()=>advance(-1);$('.album-next',controls).onclick=()=>advance(1);play.onclick=()=>{wantsAuto=!wantsAuto;schedule();};
  gallery.addEventListener('keydown',e=>{if(e.key==='ArrowLeft'){e.preventDefault();advance(-1);}if(e.key==='ArrowRight'){e.preventDefault();advance(1);}},{signal});
  let touchX=0;gallery.addEventListener('touchstart',e=>{touchX=e.touches[0].clientX;pause();},{passive:true,signal});gallery.addEventListener('touchend',e=>{const diff=e.changedTouches[0].clientX-touchX;if(Math.abs(diff)>35)advance(diff>0?-1:1);else schedule(7000);},{passive:true,signal});
  if(matchMedia('(hover:hover)').matches){gallery.addEventListener('mouseenter',()=>{hovered=true;pause();},{signal});gallery.addEventListener('mouseleave',()=>{hovered=false;schedule();},{signal});}
  gallery.addEventListener('click',pause,{signal});gallery.addEventListener('focusin',pause,{signal});gallery.addEventListener('focusout',()=>{timers.push(setTimeout(()=>schedule(),0));},{signal});
  $('#photo-dialog')?.addEventListener('close',()=>schedule(),{signal});document.addEventListener('visibilitychange',()=>schedule(),{signal});
  if('IntersectionObserver' in window){const observer=new IntersectionObserver(entries=>{inView=entries[0].isIntersecting;schedule();},{threshold:.2});observer.observe(gallery);observers.push(()=>observer.disconnect());}else{inView=true;}
  arrange();schedule();observers.push(pause);
 }
 if(opened){card.classList.add('invitation-opened');}
 if(opened&&!boot.preview){
  const dock=document.createElement('nav');dock.className='invitation-dock';dock.setAttribute('aria-label','Điều khiển thiệp');dock.innerHTML=`<button class="scroll-toggle" aria-pressed="false" aria-label="Bắt đầu tự cuộn">▶ <span>Tự cuộn</span></button>${c.guestbook.enabled?'<a href="#wish-anchor">♡ <span>Gửi lời chúc</span></a>':''}${c.rsvp.enabled?'<a href="#rsvp">✉ <span>Xác nhận</span></a>':''}${c.banks.enabled?'<a href="#gift-anchor">♧ <span>Quà mừng</span></a>':''}`;document.body.append(dock);
  $('.public-form[data-public-form="wish"]')?.closest('.wedding-section').setAttribute('id','wish-anchor');$('.gift-section')?.setAttribute('id','gift-anchor');
  const toggle=$('.scroll-toggle',dock);
  function pause(){auto=false;cancelAnimationFrame(raf);toggle.setAttribute('aria-pressed','false');toggle.setAttribute('aria-label','Bắt đầu tự cuộn');toggle.innerHTML='▶ <span>Tự cuộn</span>';}
  function tick(now){if(!auto)return;const dt=lastFrame?Math.min(now-lastFrame,50):0;lastFrame=now;position+=Number(c.experience.scrollSpeed)*dt/1000;window.scrollTo({top:position,behavior:'instant'});if(innerHeight+scrollY>=document.documentElement.scrollHeight-2){pause();return;}raf=requestAnimationFrame(tick);}
  function start(){if($('#photo-dialog')?.open||$('input:focus,textarea:focus,select:focus'))return;auto=true;position=scrollY;lastFrame=0;toggle.setAttribute('aria-pressed','true');toggle.setAttribute('aria-label','Dừng tự cuộn');toggle.innerHTML='Ⅱ <span>Dừng cuộn</span>';raf=requestAnimationFrame(tick);}
  toggle.addEventListener('click',()=>auto?pause():start(),{signal});
  let userInteracted=false;function userPause(){userInteracted=true;pause();}
  window.addEventListener('wheel',userPause,{passive:true,signal});window.addEventListener('touchstart',e=>{if(!e.target.closest('.scroll-toggle'))userPause();},{passive:true,signal});
  document.addEventListener('pointerdown',e=>{if(!e.target.closest('.scroll-toggle'))userPause();},{signal});document.addEventListener('focusin',e=>{if(e.target.matches('input,textarea,select'))userPause();},{signal});
  document.addEventListener('keydown',e=>{if(['ArrowDown','ArrowUp','PageDown','PageUp','Home','End',' '].includes(e.key))userPause();},{signal});document.addEventListener('visibilitychange',()=>{if(document.hidden)pause();},{signal});
  if(c.experience.autoScroll&&!reduced)timers.push(setTimeout(()=>{if(!userInteracted)start();},1800));
  if(c.experience.floatingWishes&&!reduced){
   const stream=document.createElement('aside');stream.className='wish-stream';stream.hidden=!wishes.length;stream.setAttribute('aria-label','Lời chúc từ khách mời');stream.innerHTML='<button class="hide-wishes" aria-label="Ẩn lời chúc nổi">×</button><div class="wish-bubbles"></div>';document.body.append(stream);let cursor=0,dismissed=false;
   $('.hide-wishes',stream).onclick=()=>{dismissed=true;stream.hidden=true;};
   function bubble(){if(!wishes.length||document.hidden||dismissed)return;stream.hidden=false;const w=wishes[cursor++%wishes.length],key=w.name+'|'+w.message;if($$('.wish-bubble',stream).some(el=>el.dataset.wish===key))return;const el=document.createElement('div');el.dataset.wish=key;el.className='wish-bubble';el.innerHTML=`<strong>${esc(w.name)}</strong><span>${esc(w.message)}</span>`;$('.wish-bubbles',stream).append(el);while($('.wish-bubbles',stream).children.length>3)$('.wish-bubbles',stream).firstElementChild.remove();el.addEventListener('animationend',()=>el.remove(),{once:true});}
   bubble();const interval=setInterval(bubble,3700);observers.push(()=>clearInterval(interval));
  }
  if(c.guestbook.enabled){
   const poll=setInterval(async()=>{if(document.hidden)return;try{const res=await fetch('api.php?action=config&card='+encodeURIComponent(boot.cardId));if(!res.ok)return;const result=await res.json();wishes=result.wishes||[];const list=$('.public-wishes');if(list)list.innerHTML=wishes.length?wishes.slice(-30).reverse().map(w=>`<blockquote><span>“</span><p>${esc(w.message)}</p><cite>— ${esc(w.name)}</cite></blockquote>`).join(''):'<p class="no-wishes">Hãy là người đầu tiên gửi lời chúc đến chúng mình ♡</p>';}catch{}},45000);observers.push(()=>clearInterval(poll));
  }
 }
 cleanup=()=>{controller.abort();cancelAnimationFrame(raf);clearInterval(galleryTimer);timers.forEach(clearTimeout);observers.forEach(fn=>fn());$$('.invitation-dock,.wish-stream').forEach(el=>el.remove());};
}
mount({config:initial.config,opened:initial.preview,boot:initial});
})();
