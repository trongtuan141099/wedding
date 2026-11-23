/* Public invitation labels only. Names, addresses and guest messages are never machine-translated. */
(() => {
'use strict';
const boot=JSON.parse(document.getElementById('boot').textContent);
const language=boot.language==='jp'?'jp':'vn';
const labels={
 'Mở thiệp':'招待状を開く','Mở thiệp ↗':'招待状を開く ↗','Tôi sẽ đến chung vui':'出席します','Rất tiếc, tôi không thể đến':'申し訳ありませんが欠席します','Thân mời bạn đến chung vui':'大切な皆さまをお招きいたします','Trân trọng kính mời':'謹んでご招待申し上げます','TRÂN TRỌNG KÍNH MỜI':'謹んでご招待申し上げます','Chú rể':'新郎','Cô dâu':'新婦','NHÀ TRAI':'新郎の家族','NHÀ GÁI':'新婦の家族','Cuộn để xem lời mời':'スクロールしてご覧ください',
 'THE WEDDING INVITATION':'挙式のご案内','OUR LITTLE LOVE STORY':'ふたりの物語','Album ảnh':'フォトアルバム','SAVE THE DATE':'大切な日','KẾT HÔN':'結婚','結婚披露宴 SẼ DIỄN RA VÀO LÚC':'披露宴の開始時刻','SẼ DIỄN RA VÀO LÚC':'開始時刻','HÂN HẠNH ĐÓN TIẾP TẠI':'会場のご案内','Mở bản đồ chỉ đường':'地図・アクセス','Mở bản đồ chỉ đường ↗':'地図・アクセス ↗','Xem chỉ đường ↗':'アクセスを見る ↗','THE CELEBRATION':'当日の流れ','Lịch trình ngày cưới':'当日のスケジュール','A TOUCH OF HARMONY':'装いのご案内','Dress code':'ドレスコード',
 'WE SAVED YOU A SEAT':'お席をご用意しております','Bạn sẽ đến chứ?':'ご出欠をお知らせください','Tên của bạn':'お名前','Nhập họ tên của bạn':'お名前をご入力ください','Chọn xác nhận':'ご出欠をお選びください','♡ Mình sẽ đến chung vui':'♡ 出席します','Rất tiếc, mình không thể đến':'申し訳ありませんが欠席します','Số người tham dự':'出席人数','Lời chúc yêu thương':'お祝いのメッセージ','Gửi lời chúc đến cô dâu và chú rể…':'新郎新婦へのメッセージをご入力ください…','Gửi lời chúc ♡':'メッセージを送る ♡','Gửi xác nhận →':'回答を送信 →','Xác nhận tham dự →':'出欠を回答する →','Đang gửi…':'送信中…','WORDS FROM THE HEART':'心を込めたメッセージ','Hãy là người đầu tiên gửi lời chúc đến chúng mình ♡':'最初のお祝いメッセージをお待ちしています ♡',
 'Tôi cần xe đưa đón':'送迎を希望します','Để gia đình sắp xếp chuyến đi chu đáo hơn.':'送迎の手配のため、ご希望をお知らせください。','Thông tin đón khách':'お迎えの詳細','Địa chỉ đón':'お迎え場所の住所','Số nhà, đường, xã/phường, tỉnh/thành':'番地・町名・市区町村・都道府県','Số người đón':'送迎人数','Điện thoại liên lạc':'連絡先の電話番号','Thông tin chỉ gửi đến người tổ chức để sắp xếp xe và liên hệ đón khách.':'この情報は送迎の手配とご連絡のため、主催者にのみ送信されます。',
 'A LITTLE GIFT OF LOVE':'心を込めたお祝い','Hộp quà mừng':'お祝いのご案内','Lưu QR':'QRを保存','Sao chép số tài khoản':'口座番号をコピー','Đã sao chép số tài khoản.':'口座番号をコピーしました。','Chọn và sao chép số tài khoản':'口座番号を選択してコピーしてください','Đóng':'閉じる','Thông tin quà mừng sẽ được cập nhật.':'お祝いの詳細は後日ご案内いたします。','Ảnh QR mở ở tab mới. Trên điện thoại, nhấn giữ ảnh để lưu.':'QR画像を新しいタブで開きました。画像を長押しすると保存できます。',
 'Ngày':'日','Giờ':'時間','Phút':'分','Giây':'秒','Ngày sẽ được cập nhật':'日程は後日ご案内いたします','Ngày hạnh phúc đã đến ♡':'いよいよ大切な日を迎えました ♡','Ngày cưới':'結婚式の日','NGÀY CHÚNG MÌNH CHUNG ĐÔI':'ふたりの新しい門出','Lịch ngày cưới':'結婚式のカレンダー','♡ Một ngày để nhớ, một đời để thương.':'♡ 忘れられない一日、一生の愛。','Đến chung vui trong ngày hạnh phúc của chúng mình.':'私たちの大切な一日をご一緒にお祝いください。',
 'Điều khiển thiệp':'招待状の操作','Bắt đầu tự cuộn':'自動スクロールを開始','Dừng tự cuộn':'自動スクロールを停止','Tự cuộn':'自動スクロール','Dừng cuộn':'停止','Gửi lời chúc':'メッセージ','Xác nhận':'出欠回答','Quà mừng':'お祝い','Lời chúc từ khách mời':'皆さまからのメッセージ','Ẩn lời chúc nổi':'メッセージ表示を隠す','Album ảnh cưới 3D':'3Dウェディングアルバム','Ảnh trước':'前の写真','Ảnh tiếp theo':'次の写真','Bật tự chuyển ảnh':'スライドショーを再生','Dừng tự chuyển ảnh':'スライドショーを停止','Đóng ảnh':'写真を閉じる','Ảnh cưới phóng to':'拡大写真','Phát nhạc nền':'音楽を再生','Tạm dừng nhạc':'音楽を一時停止','Nhạc chỉ phát trên thiệp chính.':'音楽は公開された招待状で再生できます。','Chưa phát được nhạc. Bấm nút nhạc để thử lại hoặc kiểm tra tệp âm thanh.':'音楽を再生できませんでした。音楽ボタンを押してもう一度お試しください。','Đây là bản xem trước. Mở thiệp chính để gửi thử.':'これはプレビューです。公開された招待状から送信してください。'
};
const t=value=>language==='jp'?(labels[value]??value):value;
function translate(root=document.body){
 if(language!=='jp')return;
 const protectedContent='.public-wishes blockquote,.wish-bubble,.account-copy,.hero-names h1,.couple-names h2,.family-person strong,.family-person p,.personal-invitation strong,.envelope-guest strong,.bank-cards article>strong,.bank-cards article>p';
 const walker=document.createTreeWalker(root,NodeFilter.SHOW_TEXT);let node;
 while((node=walker.nextNode())){
  if(node.parentElement?.closest('script,style,textarea,'+protectedContent))continue;
  const source=node.nodeValue.trim();if(labels[source])node.nodeValue=node.nodeValue.replace(source,labels[source]);
 }
 for(const element of root.querySelectorAll('[aria-label],[placeholder],[title],[alt]'))for(const attribute of ['aria-label','placeholder','title','alt']){
  const source=element.getAttribute(attribute);if(source&&labels[source])element.setAttribute(attribute,labels[source]);
  else if(source){const translated=source.replace(/^Lưu mã QR /,'QRを保存 ').replace(/^Xem ảnh cưới /,'ウェディングフォトを見る ').replace(/^Ảnh cưới của /,'ウェディングフォト ').replace(/^Ảnh cưới /,'ウェディングフォト ');if(translated!==source)element.setAttribute(attribute,translated);}
 }
}
async function copyText(value){
 const text=String(value);if(!text)return false;
 try{if(navigator.clipboard?.writeText){await navigator.clipboard.writeText(text);return true;}}catch{}
 const previous=document.activeElement,field=document.createElement('textarea');field.value=text;
 field.setAttribute('readonly','');field.style.cssText='position:fixed;top:0;left:0;opacity:0;font-size:16px';document.body.append(field);
 let copied=false;try{field.focus({preventScroll:true});field.select();field.setSelectionRange(0,text.length);copied=Boolean(document.execCommand?.('copy'));}catch{}
 field.remove();try{previous?.focus({preventScroll:true});}catch{}return copied;
}
document.querySelectorAll('[data-language]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();const url=new URL(location.href);url.searchParams.set('lang',link.dataset.language);location.href=url.href;}));
window.WeddingLanguage={language,locale:language==='jp'?'ja-JP':'vi-VN',t,translate,copyText};
})();
