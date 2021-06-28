#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <string.h>
#include <fcntl.h>

#define true 1
#define false 0
typedef char bool;

#define BHYVE_BIN "/usr/sbin/bhyve"

bool writeFile(char *filename, char *data)
{
    FILE *fp= NULL;
    fp = fopen (filename, "w+");
    if(fp)
    {
        fputs(data,fp);
        fflush(fp);
        fclose(fp);
        return true;
    }

    return false;
}

bool appendFile(char *filename, char *data)
{
    FILE *fp= NULL;
    fp = fopen (filename, "a+");
    if(fp)
    {
        fputs(data,fp);
        fflush(fp);
        fclose(fp);
        return true;
    }

    return false;
}

/** IS_DIR **/
bool is_dir(char *path)
{
    struct stat buf;
    if(stat(path, &buf) != 0)
    {
        return false;
    }

    return S_ISDIR(buf.st_mode);
}
/** IS_DIR **/

/** IS_FILE **/
bool is_file(char *path)
{
    struct stat buf;
    if(stat(path, &buf) != 0)
    {
        return false;
    }

    return S_ISREG(buf.st_mode);
}
/** IS_FILE **/


int main(int argc, char* argv[])
{
    pid_t PID = 0;
    pid_t SID = 0;
    int rval = 0;

    char root_path[256];
    char base_path[256];
    char prefix[64];
    char init_file[256];

    char pid_file[256];
    char log_file[256];
    char res_file[256];
    char buff[4096];
    bool run = false;

    if(argc < 3)
    {
        fprintf(stderr,"BVCP BHYVE RUNNER Helper\nUsage: %s [root_path] [prefix]\n",argv[0]);
        exit(0);
    }

    if(strlen(argv[1]) > 170 || strlen(argv[2]) > 64)
    {
        fprintf(stderr,"Arguments Too Long!\n");
        exit(2);
    }

    if(argc >= 4 && !strcmp(argv[3],"run"))
    {
        run = true;
    }


    root_path[0] = 0x00;
    strcat(root_path,argv[1]);

    prefix[0] = 0x00;
    strcat(prefix,argv[2]);

    base_path[0] = 0x00;
    sprintf(base_path,"%s/tmp/%s",root_path,prefix);

    init_file[0] = 0x00;
    sprintf(init_file,"%s/init",base_path);

    log_file[0] = 0x00;
    sprintf(log_file,"%s/log",base_path);


    if(run == true)
    {
        pid_file[0] = 0x00;
        sprintf(pid_file,"%s/vm.pid",base_path);
    }
    else
    {
        pid_file[0] = 0x00;
        sprintf(pid_file,"%s/ctl.pid",base_path);
    }

    res_file[0] = 0x00;
    sprintf(res_file,"%s/vm.exit",base_path);
    fprintf(stdout,"Root Path: %s\nBase Path: %s\nPrefix: %s\n",root_path,base_path,prefix);


    if(is_dir(base_path) == false)
    {
        fprintf(stderr,"Directory: %s, Non-exists!\n",base_path);
        exit(10);
    }

    if(is_file(init_file) == false)
    {
        fprintf(stderr,"File: %s, Non-exists!\n",init_file);
        exit(10);
    }

    if(!run)
    {
        PID = fork();
        if (PID < 0)
        {
            fprintf(stderr,"Fork Failed!\n");
            exit(2);
        }

        if (PID > 0)
        {
            buff[0] = 0x00;
            sprintf(buff,"%d",PID);
            writeFile(pid_file,buff);

            fprintf(stdout,"Started\n");
            exit(0);
        }

        umask(0);
        SID = setsid();
        if(SID < 0)
        {
            exit(1);
        }

        chdir(argv[1]);

        int fd = open(log_file, O_WRONLY, 0600);
        if(fd)
        {
            dup2(fd, STDOUT_FILENO); // Check `man stdin` for more info
            dup2(fd, STDERR_FILENO);            
        }

        close(STDIN_FILENO);
/*
        close(STDOUT_FILENO);
        close(STDERR_FILENO);
*/        
    }

    
    if(run == true)
    {
        PID = getpid();
        buff[0] = 0x00;
        sprintf(buff,"%d",PID);
        writeFile(pid_file,buff);

        FILE *fp = NULL;
        char line[256], *result;
        fp = fopen(init_file,"r");
        buff[0] = 0x00;
        char *arr[256];
        int c = 0;

        if(fp)
        {
            arr[c] = malloc(256);
            memset(arr[c],0,256);
            strcat(arr[c],BHYVE_BIN);
            fprintf(stderr,"[%d] %s\n",c,BHYVE_BIN);
            c++;
                        
            while((result = fgets(line,255,fp)) != NULL)
            {
                line[strlen(line) - 1] = 0x00;
                arr[c] = malloc(256);
                memset(arr[c],0,256);
                strcat(arr[c],line);
                fprintf(stderr,"[%d] %s\n",c,line);
                c++;
            }
            arr[c] = NULL;
            fprintf(stderr,"\n\n");

            execvp(BHYVE_BIN, arr);
        }
    }
    else
    {
        writeFile(res_file,"S");
        buff[0] = 0x00;
        sprintf(buff,"%s %s %s run",argv[0],argv[1],argv[2]);
        rval = system(buff);

        buff[0] = 0x00;
        sprintf(buff,"%d",rval);

        writeFile(res_file,buff);
        writeFile(pid_file,"");
    }

    return (0);
}