import { Component, CUSTOM_ELEMENTS_SCHEMA, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';

import { Auth, createUserWithEmailAndPassword } from '@angular/fire/auth';
import { getFirestore, doc, setDoc } from 'firebase/firestore';  // <-- import getFirestore here
import { AuthService } from 'src/app/services/chat-bot/auth/auth.service';

@Component({
  selector: 'app-sign-up',
  standalone: true,
  imports: [CommonModule, IonicModule, ReactiveFormsModule],
  templateUrl: './sign-up.component.html',
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class SignUpComponent {
  form: FormGroup;
  firestore = getFirestore(); // initialize firestore here

  constructor(private fb: FormBuilder, private auth: Auth,private authService:AuthService) {
    this.form = this.fb.group({
      name: [''],
      email: [''],
      photo: [''],
      password: ['']
    });
  }

  async signUp() {
  
    try {
      if (this.form.valid) {
        await this.authService.signUp(this.form);
      }

      console.log('User signed up and Firestore doc created');
    } catch (error) {
      console.error('Signup error:', error);
    }
  }


}
