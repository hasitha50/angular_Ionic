import { Component, Input, OnInit } from '@angular/core';
import { IonItem, IonLabel, IonIcon, IonText, IonNote } from "@ionic/angular/standalone";
import { addIcons } from 'ionicons';
import { checkmark, checkmarkDoneOutline } from 'ionicons/icons';

@Component({
  selector: 'app-chat-box',
  templateUrl: './chat-box.component.html',
  styleUrls: ['./chat-box.component.scss'],
  imports:[IonItem,IonLabel,IonText,IonNote,IonText,IonIcon]
})
export class ChatBoxComponent  implements OnInit {
  @Input() chat: any;
  @Input() current_user_id:any;
  constructor() { 
    addIcons({checkmarkDoneOutline})
  }

  ngOnInit() {}

}
