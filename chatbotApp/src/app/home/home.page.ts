import { Component } from '@angular/core';
import { IonicModule, ModalController } from '@ionic/angular';
import { CommonModule } from '@angular/common';

import { addIcons } from 'ionicons';
import { chatboxEllipsesOutline, chatboxOutline, chatbubbleEllipsesOutline } from 'ionicons/icons';
import { ChatsComponent } from '../components/chat-bot/chats.component';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, IonicModule,ChatsComponent],
  templateUrl: './home.page.html',
  styleUrls: ['./home.page.scss'],
})
export class HomePage {
  constructor(private modalCtrl: ModalController) {
    addIcons({chatboxEllipsesOutline,chatbubbleEllipsesOutline})
  }

  
}
