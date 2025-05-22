import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { IonItem, IonAvatar, IonLabel } from "@ionic/angular/standalone";

@Component({
  selector: 'app-chat-user-list',
  templateUrl: './chat-user-list.component.html',
  styleUrls: ['./chat-user-list.component.scss'],
  imports:[IonItem,IonAvatar,IonLabel]
})
export class ChatUserListComponent  implements OnInit {
  @Input() item: any;
  @Output() onClick: EventEmitter<any> = new EventEmitter();

  constructor() { }

  ngOnInit() {}

  redirect() {
    this.onClick.emit(this.item);
  }
}
