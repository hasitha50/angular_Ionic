import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import {
  IonContent, IonHeader, IonTitle, IonToolbar, IonBackButton, IonButtons,
  IonFooter, IonFabButton, IonIcon, IonSpinner, IonList, IonItemGroup,
  IonGrid, IonCol, IonRow, IonTextarea
} from '@ionic/angular/standalone';
import { ChatBoxComponent } from '../chat-box/chat-box.component';
import { addIcons } from 'ionicons';
import { send } from 'ionicons/icons';
import { ChatService } from 'src/app/services/chat-bot/chat/chat.service';
import { Observable, of } from 'rxjs';

@Component({
  selector: 'app-chat-room',
  templateUrl: './chat-room.component.html',
  styleUrls: ['./chat-room.component.scss'],
  imports: [
    IonRow, IonCol, IonGrid, IonItemGroup, IonList, IonSpinner,
    IonIcon, IonFabButton, IonButtons, IonBackButton, IonContent,
    IonHeader, IonTitle, IonToolbar, CommonModule, IonFooter,
    ReactiveFormsModule, ChatBoxComponent, IonTextarea
  ],
})
export class ChatRoomComponent implements OnInit {
  form!: FormGroup;

  @Input() chatRoomId: string = '';
  @Input() name: string = '';

  isLoading = false;
  currentUserId = this.chatService.currentUserId;

  chats: any;


  constructor(private fb: FormBuilder, private chatService: ChatService) {
    addIcons({ send });
  }

  ngOnInit() {
    this.form = this.fb.group({
      message: ['']
    });

    if (this.chatRoomId) {
      this.chatService.getChatMessages(this.chatRoomId, (msgs) => {
        this.chats = msgs;
      });
    }
  }

  async sendMessage() {
    const msg = this.form.value.message;
    try {
      await this.chatService.sendMessage(this.chatRoomId, msg);
      // this.form.reset();  // clear the textarea
    } catch (error) {
      console.error('Failed to send message:', error);
    }
  }
}
