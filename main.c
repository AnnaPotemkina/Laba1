//
//  main.c
//  labka1 final
//
//  Created by Анна Потёмкина on 26.02.2020.
//  Copyright © 2020 Анна Потёмкина. All rights reserved.
//
/* ДИНАМИЧЕСКИЙ МАССИВ
    обеспечить возможность работы со студентами или с преподавателями,
    конкатенацию, добавление нового человека и применение функции */

#include <stdio.h>
#include <stdlib.h>
#include <time.h>


struct studant {
    char* firstName;
    char* middleName;
    char* lastName;
    time_t birthDate;
};

struct teacher {
    char* firstName;
    char* middleName;
    char* lastName;
    time_t birthDate;
};

typedef struct studant student;
typedef struct teacher teacher;

char *read_str(){ //считываем строку
    int c, i = 0;
    char *s = (char *)malloc(0 * sizeof(char));
    while ((c = getchar()) != '\n'){
        s = (char *)realloc(s, (i + 1) * sizeof(char));
        s[i] = (char)c;
        ++i;
    }
    s = (char *)realloc(s, i  * sizeof(char));
    s[i] = '\0';
    return s;
}

student *create_student(){
    student *buff = (student *)malloc(sizeof(student));
    return buff;
}

teacher *create_teacher(){
    teacher *buff = (teacher *)malloc(sizeof(teacher));
    return buff;
}
void fill_array(void **arr, char type, int n){ //заполняем массив
    if (type == 't'){ //если работаем с преподавателями
        for (int i = 0; i < n; ++i){
            printf("введите имя человека ");
            char *firstName = read_str();
             printf("введите отчество человека ");
            char *middleName = read_str();
             printf("введите фамилия человека ");
            char *lastName = read_str();
            ((teacher *)arr[i])->firstName=firstName;
            ((teacher *)arr[i])->lastName=lastName;
            ((teacher *)arr[i])->middleName=middleName;
        }
    }
    else if(type == 's'){ //если работаем со студентами
        for (int i = 0; i < n; ++i){
             printf("введите имя человека ");
            char *firstName = read_str();
             printf("введите отчество человека ");
            char *middleName = read_str();
             printf("введите фамилию человека ");
            char *lastName = read_str();
            ((student *)arr[i])->firstName=firstName;
            ((student *)arr[i])->lastName=lastName;
            ((student *)arr[i])->middleName=middleName;
        }
    }
}

 void fill_aarray(void **arr, char type, int n){ //дозаполняем массив
    if (type == 't'){ //если работаем с преподавателями
        for (int i = (n-1); i < n; ++i){
            printf("введите имя человека ");
            char *firstName = read_str();
             printf("введите отчество человека ");
            char *middleName = read_str();
             printf("введите фамилия человека ");
            char *lastName = read_str();
            ((teacher *)arr[i])->firstName=firstName;
            ((teacher *)arr[i])->lastName=lastName;
            ((teacher *)arr[i])->middleName=middleName;
        }
    }
    else if(type == 's'){ //если работаем со студентами
        for (int i = (n-1); i < n; ++i){
             printf("введите имя человека ");
            char *firstName = read_str();
             printf("введите отчество человека ");
            char *middleName = read_str();
             printf("введите фамилию человека ");
            char *lastName = read_str();
            ((student *)arr[i])->firstName=firstName;
            ((student *)arr[i])->lastName=lastName;
            ((student *)arr[i])->middleName=middleName;
        }
    }
}



void print_array(void **arr, char type, int n){ //вывод массива
    if (type == 't'){ //если работаем с преподавателями
        for (int i = 0; i < n; ++i){
            printf("First name: %s\tLast name: %s\tMiddle name: %s\n", ((teacher *)arr[i])->firstName, ((teacher *)arr[i])->lastName,((teacher *)arr[i])->middleName);
        }
    }
    else if(type == 's'){ //если работаем со студентами
        for (int i = 0; i < n; ++i){
            char *firstName = read_str();
            char *middleName = read_str();
            char *lastName = read_str();
            ((student *)arr[i])->firstName=firstName;
            ((student *)arr[i])->lastName=lastName;
            ((student *)arr[i])->middleName=middleName;
        }
    }
}

void **create_array(int n, char type){ //создаем массив
    void **arr = (void **)malloc(n * sizeof(void *));
    if (type == 't'){ // если работаем с преподавателми
        for (int i = 0; i < n; ++i){
            arr[i] = create_teacher();
        }
    }
    else if (type == 's'){ // если работаем со студентами
        for (int i = 0; i < n; ++i){
            arr[i] = create_student();
        }
    }
    return arr;
}

void **concat_arr(void **arr1, int n1, void **arr2, int n2){ //конкатенация
    void **buff = (void **)malloc((n1 + n2) * sizeof(void *));
    memcpy(buff, arr1, n1 * sizeof(void *));
    memcpy((char *)buff + n1 * sizeof(void *), arr2, n2 * sizeof(void *));
    
    return buff;
}

void f_for_teacher(void *st){ //функция для преподавателей
    teacher *n_st = (teacher *)st;
    n_st->firstName = "teacher";
}

void f_for_student(void *st){ //функция для студентов
   struct studant *n_st = (student*)st;
    n_st->firstName = "studant";
}

void map(void (*f) (void *), void **arr, int n, char type){
    if (type == 't'){ //применение функции к преподавателю
        for (int i = 0; i < n; ++i) {
            f((teacher *)arr[i]);
        }
    }
    else if (type == 's'){ //применение функции к студенту
        for (int i = 0; i < n; ++i) {
            f((student *)arr[i]);
        }
    }
}

int main() {
    int n1;
    printf("если вы хотите работать с преподавателями, нажмите t, если со студентами - s");
    char t=getchar();
    if ((t!= 't') && (t!='s')){
        printf("неверный ввод");
    }
    else{
        printf("введите количество человек ");
        scanf("%d", &n1);
        getchar();
        void **arr1 = create_array(n1, t);
        fill_array(arr1, t, n1);
        print_array(arr1, t, n1);
        int n2;
        printf("введите количество человек ");
        scanf("%d", &n2);
        getchar();
        void **arr2 = create_array(n2, t);
        fill_array(arr2, t, n2);
        void** arr3;
        int n3;
        int f=1;
    
    
        while (f!=0){
            printf("1 применить функцию/т 2, чтобы ввести еще одного человека/n  3 конкатенация/n  0 конец программы/n ");
            scanf("%d",&f);
            getchar();
            if(f==2){
                n1=n1+1;
                arr1 = realloc(arr1,sizeof(void*)*n1);
                if (t=='t'){
                     arr1[n1-1] = create_teacher();
                }
                else{
                     arr1[n1-1] = create_student();
                }
                fill_aarray(arr1, t, n1);
                print_array(arr1, t, n1);
            }
            if (f==3){
                void **arr3 = concat_arr(arr1, n1, arr2, n2);
                print_array(arr3, t, n1 + n2);
            }
            if(f==1){
                printf("к какой группе вы хотите применить функцию? 1 или 2 ");
                int typ;
                scanf("%d", &typ);
                getchar();
                if (typ==1){
                   if (t=='t'){
                       map(&(f_for_teacher), arr1, n1, 't');
                       print_array(arr1, 't', n1);
                   }
                   else{
                       map(&(f_for_student), arr1, n1, 't');
                       print_array(arr1, 't', n1);
                   }
                }
                else{
                    
                    if (t=='t'){
                        map(&(f_for_teacher), arr2, n2, 't');
                        print_array(arr2, 't', n2);
                    }
                    else{
                        map(&(f_for_student), arr2, n2, 't');
                        print_array(arr2, 't', n2);
                    }
                }
            }
        
        }
        free(arr1);
        free(arr2);
        free(arr3);
    }
 
    return 0;
    
}
