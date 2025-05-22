import { Component, CUSTOM_ELEMENTS_SCHEMA, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { 
  IonContent, IonHeader, IonTitle, IonToolbar, IonButtons, IonButton, IonIcon, 
  IonLabel, IonPopover, IonList, IonItem, IonListHeader, IonAvatar, IonText 
} from '@ionic/angular/standalone';
import { Router } from '@angular/router';
import { ModalController, IonicModule, PopoverController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import { 
  add,
  arrowBack, chatbubbleEllipsesOutline, closeOutline, ellipsisVertical, 
  ellipsisVerticalOutline, logInOutline, personAddOutline 
} from 'ionicons/icons';
import { signOut } from 'firebase/auth';
import { ChatRoomComponent } from './chat-room/chat-room.component';
import { SignInComponent } from './auth/sign-in/sign-in.component';
import { SignUpComponent } from './auth/sign-up/sign-up.component';
import { Auth } from '@angular/fire/auth';
import { ChatUserListComponent } from './chat-user-list/chat-user-list.component';
import { Observable, of } from 'rxjs';
import { ChatService } from 'src/app/services/chat-bot/chat/chat.service';

@Component({
  selector: 'app-chats',
  templateUrl: './chats.component.html',
  styleUrls: ['./chats.component.scss'],
  imports: [
    IonText, IonAvatar, IonListHeader, IonItem, IonList, IonPopover,
    IonLabel, IonIcon, IonButton, IonButtons, IonContent, IonHeader,
    IonTitle, IonToolbar, CommonModule, FormsModule, ChatRoomComponent,
    SignInComponent, SignUpComponent,ChatUserListComponent
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class ChatsComponent implements OnInit {
  segment = 'chats';
  openNewChat = false;
  isChatModalOpen = false;
  selectedChat: any = null;
  participantName: string = 'null';

  userRole = this.getCookie('botUserRole');
  isLogin = this.getCookie('isBotLogin');
  isSignupForm = false;

  chatRooms:any;
  users: any;
  constructor(
    private router: Router,
    private modalCtrl: ModalController,
    private auth: Auth,
    private chatService:ChatService
  ) {
    addIcons({
      closeOutline, chatbubbleEllipsesOutline, ellipsisVerticalOutline,
      ellipsisVertical, arrowBack, logInOutline, personAddOutline,add
    });
    
  }

  ngOnInit() {
    this.users = this.chatService.getUsers();
    this.chatRooms=this.chatService.getChatRooms();
  }

  logout() {
    signOut(this.auth)
      .then(() => {
        console.log('User signed out');
        document.cookie = 'token=; Max-Age=0; path=/; secure';
        document.cookie = 'isBotLogin=; Max-Age=0; path=/; secure';
        location.reload();
      })
      .catch((error) => console.error('Logout error:', error));
  }

  openChat() {
    this.isChatModalOpen = true;
  }

  closeChat() {
    this.isChatModalOpen = false;
  }

  onSegmentChanged(event: any) {
    console.log('Segment changed:', event);
  }


  cancel() {
    this.openNewChat = false;
  }

  newChat() {
    this.openNewChat = true;
    if(!this.users){
      this.getUsers()
    }
  }

  getUsers() {
    this.chatService.getUsers().subscribe(users => {
      this.users = users;
    });
  }
  

  async startChat(item: any) {
    console.log('Starting chat with', item);
    const room =await this.chatService.createChatRoom(item?.id)
    this.openNewChat = false;
    
    this.getChat(room?.id,item?.name)
  }

  getChat(item: any,participantName:any) {
    this.selectedChat = item;
    this.participantName= participantName
  }

  pageBack() {
    this.selectedChat = null;
  }

  getCookie(name: string): string | null {
    const matches = document.cookie.match(
      new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)')
    );
    return matches ? decodeURIComponent(matches[1]) : null;
  }

  onSwitchClick() {
    this.isSignupForm = !this.isSignupForm;
  }
}
