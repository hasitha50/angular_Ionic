import { Injectable } from '@angular/core';
import {
  Auth,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut,
  User,
} from '@angular/fire/auth';
import {
  doc,
  getDoc,
  setDoc,
  serverTimestamp,
  Firestore,
} from '@angular/fire/firestore';
import { FormGroup } from '@angular/forms';
import { Observable, from, BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private currentUserSubject = new BehaviorSubject<User | null>(null);

  constructor(private auth: Auth, private firestore: Firestore) {
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
      const parsedUser = JSON.parse(savedUser);
      this.currentUserSubject.next(parsedUser);
    }

  }

  //  Sign Up
  async signUp(form: FormGroup): Promise<void> {
    const { name, email, password, photo } = form.value;

    try {
      const userCredential = await createUserWithEmailAndPassword(this.auth, email, password);
      const user = userCredential.user;
      if (!user) throw new Error('User creation failed');

      const userDoc = {
        uid: user.uid,
        name,
        email: user.email,
        role: 'user',
        photo: photo || 'assets/default-avatar.png',
        createdAt: serverTimestamp(),
      };

      await setDoc(doc(this.firestore, 'users', user.uid), userDoc);
      console.log('User signed up and Firestore document created');
    } catch (error: any) {
      console.error(' Signup error:', error.message);
    }
  }

  // Sign In
  async signIn(form: FormGroup): Promise<void> {
    const { email, password } = form.value;

    try {
      const result = await signInWithEmailAndPassword(this.auth, email, password);
      const user = result.user;
      if (!user) throw new Error('User not found');

      const token = await user.getIdToken();
      const uid = user.uid;

      const userDocRef = doc(this.firestore, 'users', uid);
      const userSnapshot = await getDoc(userDocRef);
      if (!userSnapshot.exists()) throw new Error('User document not found');

      const userData = userSnapshot.data();
      const role = userData?.['role'] ?? 'user';

      localStorage.setItem('currentUser', JSON.stringify({
        uid,
        email: user.email,
        role,
        token,
      }));
      this.setCookie('botToken', token, 7);
      this.setCookie('isBotLogin', 'true', 7);
      this.setCookie('botUserRole', role, 7);

      location.reload();
    } catch (err: any) {
      console.error('Sign-in error:', err.message || err);
    }
  }

  //  Set Cookie
  private setCookie(name: string, value: string, days: number): void {
    const maxAge = 60 * 60 * 24 * days;
    document.cookie = `${name}=${value}; path=/; max-age=${maxAge}; secure`;
  }

  //  Sign Out
  logout(): Observable<void> {
    return from(signOut(this.auth));
  }

  //  Get Current User (snapshot)
  getCurrentUser(): User | null {
    return this.currentUserSubject.value;
  }
  // Get current user ID
  getCurrentUserId(): string | null {
    return this.currentUserSubject.value?.uid || null;
  }

  //  Auth State Observable
  getAuthState(): Observable<User | null> {
    return this.currentUserSubject.asObservable();
  }
}
